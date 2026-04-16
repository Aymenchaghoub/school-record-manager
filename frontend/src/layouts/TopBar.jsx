import { Link } from 'react-router-dom';
import { useTheme } from '../context/ThemeContext';

export function TopBar({ user, onLogout, onToggleSidebar }) {
  const { isDark, toggleTheme } = useTheme();
  const handleLogout = () => onLogout?.();

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

          <div className="flex items-center gap-3">
            <Link
              to="/profile"
              className="flex items-center gap-2 rounded-lg px-3 py-1.5 transition-colors hover:bg-gray-100 dark:hover:bg-gray-800"
              aria-label="Voir mon profil"
            >
              <div className="flex h-9 w-9 select-none items-center justify-center rounded-full bg-blue-600 text-sm font-bold text-white ring-2 ring-blue-200 dark:ring-blue-900">
                {user?.first_name?.[0]?.toUpperCase() ?? user?.name?.[0]?.toUpperCase() ?? '?'}
              </div>
              <div className="hidden flex-col leading-tight sm:flex">
                <span className="text-sm font-semibold text-gray-900 dark:text-gray-100">
                  {user?.first_name ? `${user.first_name} ${user?.last_name ?? ''}`.trim() : user?.name || 'Utilisateur'}
                </span>
                <span className="text-xs capitalize text-gray-500 dark:text-gray-400">{user?.role || '-'}</span>
              </div>
            </Link>

            <div className="h-6 w-px bg-gray-200 dark:bg-gray-700" aria-hidden="true" />

            <button
              type="button"
              onClick={handleLogout}
              className="flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-sm font-medium text-red-600 transition-colors hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-900/30"
              aria-label="Se deconnecter"
            >
              <svg
                xmlns="http://www.w3.org/2000/svg"
                width="16"
                height="16"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                strokeWidth="2"
                strokeLinecap="round"
                strokeLinejoin="round"
                aria-hidden="true"
              >
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
                <polyline points="16 17 21 12 16 7" />
                <line x1="21" y1="12" x2="9" y2="12" />
              </svg>
              <span className="hidden sm:inline">Deconnexion</span>
            </button>
          </div>
        </div>
      </div>
    </header>
  );
}
