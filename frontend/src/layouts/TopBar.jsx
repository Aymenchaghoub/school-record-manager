import { Link } from 'react-router-dom';
import { Button } from '../components/ui/Button';
import { useTheme } from '../context/ThemeContext';

export function TopBar({ user, onLogout, onToggleSidebar }) {
  const { isDark, toggleTheme } = useTheme();

  return (
    <header className="app-topbar sticky top-0 z-30 border-b px-4 py-3 backdrop-blur lg:px-8">
      <div className="flex items-center justify-between">
        <button
          type="button"
          onClick={onToggleSidebar}
          className="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-sm font-medium transition-colors duration-150 hover:bg-slate-100/20 md:hidden"
          style={{ borderColor: 'var(--border)', color: 'var(--fg)' }}
          aria-label="Basculer le menu de navigation"
        >
          <span aria-hidden="true">
            <svg viewBox="0 0 24 24" className="h-4 w-4" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M4 7H20" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
              <path d="M4 12H20" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
              <path d="M4 17H20" stroke="currentColor" strokeWidth="2" strokeLinecap="round" />
            </svg>
          </span>
          Menu
        </button>

        <div className="ml-auto flex items-center gap-3">
          <button
            type="button"
            onClick={toggleTheme}
            className="theme-toggle-btn"
            aria-label={isDark ? 'Activer le mode clair' : 'Activer le mode sombre'}
            title={isDark ? 'Mode clair' : 'Mode sombre'}
          >
            <span aria-hidden="true">{isDark ? '☀' : '☾'}</span>
          </button>

          <Link
            to="/profile"
            className="flex items-center gap-2 rounded-lg px-3 py-1.5 transition-colors hover:bg-gray-100 dark:hover:bg-gray-700"
            aria-label="Voir mon profil"
          >
            <div className="flex h-8 w-8 select-none items-center justify-center rounded-full bg-blue-600 text-sm font-semibold text-white">
              {user?.first_name?.[0]?.toUpperCase() ?? user?.name?.[0]?.toUpperCase() ?? '?'}
            </div>
            <span className="hidden text-sm font-medium text-gray-700 dark:text-gray-200 sm:block">
              {user?.first_name ? `${user.first_name} ${user?.last_name ?? ''}`.trim() : user?.name || 'Utilisateur'}
            </span>
          </Link>
          <Button variant="secondary" onClick={onLogout}>
            Deconnexion
          </Button>
        </div>
      </div>
    </header>
  );
}
