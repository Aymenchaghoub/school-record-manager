import { useState } from 'react';
import { useAuth } from '../../hooks/useAuth';
import { authService } from '../../services/authService';
import { Input } from '../../components/ui/Input';
import { Button } from '../../components/ui/Button';

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

  const handleChange = (event) => {
    const { name: field, value } = event.target;
    setForm((previous) => ({ ...previous, [field]: value }));
  };

  const handleSubmit = async (event) => {
    event.preventDefault();
    setError(null);

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
      setSuccess(true);
      setTimeout(() => setSuccess(false), 3000);
    } catch (err) {
      setError(err?.message ?? 'Une erreur est survenue');
    }
  };

  return (
    <div className="mx-auto max-w-xl px-4 py-8">
      <h1 className="mb-6 text-2xl font-bold">Mon profil</h1>
      {success ? <p className="mb-4 text-green-600">Profil mis a jour avec succes.</p> : null}
      {error ? <p className="mb-4 text-red-600">{error}</p> : null}

      <form onSubmit={handleSubmit} className="space-y-4">
        <Input
          label="Prenom"
          name="first_name"
          value={form.first_name}
          onChange={handleChange}
          required
        />

        <Input
          label="Nom"
          name="last_name"
          value={form.last_name}
          onChange={handleChange}
          required
        />

        <Input
          label="Email"
          name="email"
          type="email"
          value={form.email}
          onChange={handleChange}
          required
        />

        <Input
          label="Nouveau mot de passe (laisser vide pour ne pas changer)"
          name="password"
          type="password"
          value={form.password}
          onChange={handleChange}
        />

        <Input
          label="Confirmer le mot de passe"
          name="password_confirmation"
          type="password"
          value={form.password_confirmation}
          onChange={handleChange}
        />

        <Button type="submit" variant="primary">Enregistrer</Button>
      </form>
    </div>
  );
}
