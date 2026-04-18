import { useState } from 'react';
import { useAuth } from '../../hooks/useAuth';
import { authService } from '../../services/authService';
import { Alert } from '../../components/ui/Alert';
import { Input } from '../../components/ui/Input';
import { Button } from '../../components/ui/Button';
import FR from '../../i18n/fr';

function splitDisplayName(user) {
  if (user?.first_name || user?.last_name) {
    return {
      firstName: user?.first_name || '',
      lastName: user?.last_name || '',
    };
  }

  const parts = String(user?.name || '')
    .trim()
    .split(/\s+/)
    .filter(Boolean);

  return {
    firstName: parts[0] || '',
    lastName: parts.slice(1).join(' '),
  };
}

export default function ProfilePage() {
  const { user, refreshUser } = useAuth();
  const name = splitDisplayName(user);

  const [form, setForm] = useState({
    first_name: name.firstName,
    last_name: name.lastName,
    email: user?.email ?? '',
    password: '',
    password_confirmation: '',
  });
  const [success, setSuccess] = useState(false);
  const [error, setError] = useState(null);
  const [formErrors, setFormErrors] = useState({});

  const handleChange = (event) => {
    const { name: field, value } = event.target;
    setForm((previous) => ({ ...previous, [field]: value }));

    setFormErrors((previous) => {
      if (!previous[field]) {
        return previous;
      }

      const nextErrors = { ...previous };
      delete nextErrors[field];
      return nextErrors;
    });
  };

  const validateForm = () => {
    const nextErrors = {};

    if (!String(form.first_name || '').trim()) {
      nextErrors.first_name = FR.profile.errors.firstNameRequired;
    }

    if (!String(form.last_name || '').trim()) {
      nextErrors.last_name = FR.profile.errors.lastNameRequired;
    }

    const emailValue = String(form.email || '').trim();
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailValue) {
      nextErrors.email = FR.profile.errors.emailRequired;
    } else if (!emailRegex.test(emailValue)) {
      nextErrors.email = FR.common.errors.emailInvalid;
    }

    if (form.password) {
      if (String(form.password).length < 8) {
        nextErrors.password = FR.profile.errors.passwordMin;
      }

      if (!form.password_confirmation) {
        nextErrors.password_confirmation = FR.profile.errors.passwordConfirmRequired;
      } else if (form.password_confirmation !== form.password) {
        nextErrors.password_confirmation = FR.profile.errors.passwordConfirmMismatch;
      }
    } else if (form.password_confirmation) {
      nextErrors.password = FR.profile.errors.passwordMissing;
    }

    return nextErrors;
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError(null);

    const validationErrors = validateForm();
    if (Object.keys(validationErrors).length > 0) {
      setFormErrors(validationErrors);
      setError(FR.common.errors.fixFields);
      return;
    }

    try {
      const payload = await authService.updateProfile(form);
      const updatedUser = payload?.data ?? payload?.user ?? payload;

      if (updatedUser) {
        setForm((previous) => ({
          ...previous,
          first_name: updatedUser.first_name ?? previous.first_name,
          last_name: updatedUser.last_name ?? previous.last_name,
          email: updatedUser.email ?? previous.email,
          password: '',
          password_confirmation: '',
        }));
      }

      await refreshUser();
      setFormErrors({});
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
    } catch (err) {
      setError(err?.message ?? FR.profile.errors.updateFailed);
    }
  };

  return (
    <div className="mx-auto max-w-xl px-4 py-8">
      <h1 className="mb-6 text-2xl font-bold">Mon profil</h1>
      {success ? <Alert variant="success">{FR.profile.success}</Alert> : null}
      {error ? <Alert variant="danger">{error}</Alert> : null}

      <form onSubmit={handleSubmit} className="space-y-4">
        <Input
          label="Prenom"
          name="first_name"
          value={form.first_name}
          onChange={handleChange}
          placeholder="ex: Sarah"
          helperText="Utilise pour l affichage de votre profil."
          error={formErrors.first_name}
          required
        />

        <Input
          label="Nom"
          name="last_name"
          value={form.last_name}
          onChange={handleChange}
          placeholder="ex: Benali"
          helperText="Utilise pour l affichage officiel dans l application."
          error={formErrors.last_name}
          required
        />

        <Input
          label="Email"
          name="email"
          type="email"
          value={form.email}
          onChange={handleChange}
          placeholder="ex: prenom.nom@ecole.fr"
          helperText="Cette adresse servira a la connexion et aux notifications."
          error={formErrors.email}
          required
        />

        <Input
          label="Nouveau mot de passe (laisser vide pour ne pas changer)"
          name="password"
          type="password"
          value={form.password}
          onChange={handleChange}
          placeholder="Minimum 8 caracteres"
          helperText="Utilisez au moins 8 caracteres avec lettres et chiffres pour plus de securite."
          error={formErrors.password}
        />

        <Input
          label="Confirmer le mot de passe"
          name="password_confirmation"
          type="password"
          value={form.password_confirmation}
          onChange={handleChange}
          placeholder="Retapez le nouveau mot de passe"
          helperText="Doit correspondre exactement au mot de passe saisi ci-dessus."
          error={formErrors.password_confirmation}
        />

        <Button type="submit" variant="primary">Enregistrer</Button>
      </form>
    </div>
  );
}
