import { formatRole } from '../utils/format';
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
          aria-label="Toggle navigation menu"
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
            aria-label={isDark ? 'Switch to light mode' : 'Switch to dark mode'}
            title={isDark ? 'Light mode' : 'Dark mode'}
          >
            <span aria-hidden="true">{isDark ? '☀' : '☾'}</span>
          </button>

          <div className="hidden text-right md:block">
            <p className="text-sm font-semibold" style={{ color: 'var(--fg)' }}>
              {user?.name || 'Utilisateur'}
            </p>
            <p className="text-xs theme-muted">{formatRole(user?.role)}</p>
          </div>
          <Button variant="secondary" onClick={onLogout}>
            Logout
          </Button>
        </div>
      </div>
    </header>
  );
}
