import { Link } from 'react-router-dom';
import { Button } from '../components/ui/Button';

export function UnauthorizedPage() {
  return (
    <div className="flex min-h-screen items-center justify-center px-4">
      <div className="surface-card max-w-lg p-8 text-center">
        <p className="text-xs font-semibold uppercase tracking-[0.2em]" style={{ color: 'var(--color-danger)' }}>403</p>
        <h1 className="mt-2 text-3xl font-bold" style={{ color: 'var(--color-text)' }}>Acces refuse</h1>
        <p className="mt-3" style={{ color: 'var(--color-muted)' }}>
          Votre role ne permet pas d'acceder a cette section.
        </p>
        <Link
          to="/dashboard"
          className="mt-6 inline-flex"
        >
          <Button variant="primary">Retour au tableau de bord</Button>
        </Link>
      </div>
    </div>
  );
}
