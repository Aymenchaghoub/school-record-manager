import { Link } from 'react-router-dom';

export function NotFoundPage() {
  return (
    <div className="flex min-h-screen items-center justify-center px-4">
      <div className="surface-card max-w-lg p-8 text-center">
        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">404</p>
        <h1 className="mt-2 text-3xl font-bold text-slate-900">Page introuvable</h1>
        <p className="mt-3 text-slate-600">La page demandee n'existe pas ou a ete deplacee.</p>
        <Link
          to="/dashboard"
          className="mt-6 inline-flex rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
        >
          Aller au dashboard
        </Link>
      </div>
    </div>
  );
}
