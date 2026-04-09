import { Link } from 'react-router-dom';

export function UnauthorizedPage() {
  return (
    <div className="flex min-h-screen items-center justify-center px-4">
      <div className="surface-card max-w-lg p-8 text-center">
        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-red-600">403</p>
        <h1 className="mt-2 text-3xl font-bold text-slate-900">Acces refuse</h1>
        <p className="mt-3 text-slate-600">
          Votre role ne permet pas d'acceder a cette section.
        </p>
        <Link
          to="/dashboard"
          className="mt-6 inline-flex rounded-xl bg-cyan-600 px-4 py-2 text-sm font-semibold text-white transition hover:bg-cyan-700"
        >
          Retour au dashboard
        </Link>
      </div>
    </div>
  );
}
