import { useEffect, useState } from 'react';
import { useLocation, useNavigate } from 'react-router-dom';
import { useAuth } from '../../hooks/useAuth';
import { Alert } from '../../components/ui/Alert';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';

export function LoginPage() {
  const { login, isAuthenticated, user } = useAuth();
  const navigate = useNavigate();
  const location = useLocation();

  const [email, setEmail] = useState('');
  const [password, setPassword] = useState('');
  const [remember, setRemember] = useState(true);
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [error, setError] = useState('');

  useEffect(() => {
    if (isAuthenticated && user) {
      navigate('/dashboard', { replace: true });
    }
  }, [isAuthenticated, user, navigate]);

  const submit = async (event) => {
    event.preventDefault();
    setError('');
    setIsSubmitting(true);

    try {
      await login({ email, password, remember });
      const nextPath = location.state?.from?.pathname || '/dashboard';
      navigate(nextPath, { replace: true });
    } catch (err) {
      const backendMessage = err?.message || err?.original?.response?.data?.message;
      const validationErrors = err?.original?.response?.data?.errors;
      const firstValidationError = validationErrors
        ? Object.values(validationErrors).flat()[0]
        : '';

      setError(backendMessage || firstValidationError || 'Echec de connexion.');
    } finally {
      setIsSubmitting(false);
    }
  };

  return (
    <div className="flex min-h-screen items-center justify-center px-4 py-12" style={{ background: 'var(--color-bg)' }}>
      <div
        className="w-full max-w-md p-8"
        style={{
          borderRadius: '20px',
          border: '1px solid var(--color-border)',
          background: 'var(--color-surface)',
          boxShadow: 'var(--shadow-card)',
        }}
      >
        <div className="mb-6">
          <div className="mb-2 flex items-center gap-2">
            <div
              className="flex h-8 w-8 items-center justify-center rounded-full text-sm font-semibold"
              style={{ background: 'var(--color-primary-gradient)', color: '#ffffff' }}
              aria-hidden="true"
            >
              S
            </div>
            <p
              className="text-sm font-semibold uppercase tracking-[0.16em]"
              style={{
                background: 'var(--color-primary-gradient)',
                WebkitBackgroundClip: 'text',
                backgroundClip: 'text',
                color: 'transparent',
              }}
            >
              SchoolSphere
            </p>
          </div>
          <h1 className="mt-2" style={{ fontSize: 'var(--font-size-h2)', fontWeight: 700, color: 'var(--color-text)' }}>
            Connexion
          </h1>
          <p className="mt-1" style={{ fontSize: '13px', color: 'var(--color-muted)' }}>
            Accedez a votre espace selon votre role.
          </p>
        </div>

        {error ? <Alert variant="danger">{error}</Alert> : null}

        <form className="mt-4 space-y-4" onSubmit={submit}>
          <Input
            label="Email"
            type="email"
            value={email}
            onChange={(event) => setEmail(event.target.value)}
            placeholder="admin@school.com"
            required
          />

          <Input
            label="Mot de passe"
            type="password"
            value={password}
            onChange={(event) => setPassword(event.target.value)}
            placeholder="********"
            required
          />

          <label className="flex items-center gap-2 text-sm" style={{ color: 'var(--color-muted)' }}>
            <input
              type="checkbox"
              checked={remember}
              onChange={(event) => setRemember(event.target.checked)}
            />
            Se souvenir de moi
          </label>

          <Button type="submit" className="w-full" isLoading={isSubmitting}>
            Se connecter
          </Button>
        </form>
      </div>
    </div>
  );
}
