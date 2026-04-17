import { Link } from 'react-router-dom';
import { Button } from '../components/ui/Button';

export function NotFoundPage() {
  return (
    <div className="flex min-h-screen items-center justify-center px-4">
      <div className="surface-card max-w-lg p-8 text-center">
        <p className="text-xs font-semibold uppercase tracking-[0.2em]" style={{ color: 'var(--color-muted)' }}>404</p>
        <h1 className="mt-2 text-3xl font-bold" style={{ color: 'var(--color-text)' }}>Page introuvable</h1>
        <p className="mt-3" style={{ color: 'var(--color-muted)' }}>La page demandee n'existe pas ou a ete deplacee.</p>
        <Link
          to="/dashboard"
          className="mt-6 inline-flex"
        >
          <Button variant="primary">Aller au tableau de bord</Button>
        </Link>
      </div>
    </div>
  );
}
