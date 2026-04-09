import { formatRole } from '../utils/format';
import { Button } from '../components/ui/Button';

export function TopBar({ user, onLogout, onToggleSidebar }) {
  return (
    <header className="sticky top-0 z-30 border-b border-slate-200 bg-white/95 px-4 py-3 backdrop-blur lg:px-8">
      <div className="flex items-center justify-between">
        <button
          type="button"
          onClick={onToggleSidebar}
          className="rounded-md border border-slate-200 px-3 py-1 text-sm font-medium text-slate-600 lg:hidden"
        >
          Menu
        </button>

        <div className="ml-auto flex items-center gap-3">
          <div className="hidden text-right md:block">
            <p className="text-sm font-semibold text-slate-900">{user?.name || 'Utilisateur'}</p>
            <p className="text-xs text-slate-500">{formatRole(user?.role)}</p>
          </div>
          <Button variant="secondary" onClick={onLogout}>
            Logout
          </Button>
        </div>
      </div>
    </header>
  );
}
