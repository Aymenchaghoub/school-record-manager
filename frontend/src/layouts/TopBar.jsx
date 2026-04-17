import { Link, useLocation } from 'react-router-dom';
import { useTheme } from '../context/ThemeContext';

const routeLabelMap = {
  dashboard: 'Tableau de bord',
  users: 'Utilisateurs',
  classes: 'Classes',
  subjects: 'Matieres',
  grades: 'Notes',
  absences: 'Absences',
  'report-cards': 'Bulletins',
  events: 'Evenements',
  profile: 'Mon profil',
};

const roleBadgeClassMap = {
  admin: 'role-badge-admin',
  teacher: 'role-badge-teacher',
  student: 'role-badge-student',
  parent: 'role-badge-parent',
};

const roleLabelMap = {
  admin: 'ADMINISTRATEUR',
  teacher: 'ENSEIGNANT',
  student: 'ELEVE',
  parent: 'PARENT',
};

export function TopBar({ user, onToggleSidebar }) {
  const { isDark, toggleTheme } = useTheme();
  const location = useLocation();

  const currentSegment = location.pathname.split('/').filter(Boolean).slice(-1)[0] || 'dashboard';
  const currentLabel = routeLabelMap[currentSegment] || 'Application';

  const role = user?.role || 'student';
  const notificationCount = Number(user?.notifications_count || user?.unread_notifications || 0);
  const roleBadgeClass = roleBadgeClassMap[role] || 'role-badge-student';
  const roleLabel = roleLabelMap[role] || String(role || '-').toUpperCase();
  const initial = (user?.first_name?.[0] || user?.name?.[0] || '?').toUpperCase();

  return (
    <header className="app-topbar sticky top-0 z-30 border-b px-4 py-3 backdrop-blur lg:px-8">
      <div className="flex items-center gap-3">
        <button
          type="button"
          onClick={onToggleSidebar}
          className="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-sm font-medium transition-colors duration-150 md:hidden"
          style={{ borderColor: 'var(--color-border)', color: 'var(--color-text)' }}
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

        <div className="hidden min-w-0 items-center gap-2 md:flex">
          <span
            className="inline-flex h-7 w-7 items-center justify-center rounded-full"
            style={{ background: 'var(--color-primary-gradient)', color: '#ffffff' }}
            aria-hidden="true"
          >
            <svg viewBox="0 0 24 24" width="14" height="14" fill="currentColor" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 3 3 10h2v10h5v-6h4v6h5V10h2L12 3z" />
            </svg>
          </span>
          <span style={{ color: 'var(--color-muted)', fontSize: '13px' }}>/</span>
          <strong className="truncate" style={{ color: 'var(--color-text)', fontSize: '13px' }}>
            {currentLabel}
          </strong>
        </div>

        <div className="mx-2 hidden flex-1 lg:block">
          <input
            type="search"
            className="topbar-search"
            placeholder="Rechercher un eleve, une classe..."
            aria-label="Recherche globale"
          />
        </div>

        <div className="ml-auto flex items-center gap-3">
          <button
            type="button"
            onClick={toggleTheme}
            className="topbar-icon-btn"
            aria-label={isDark ? 'Activer le mode clair' : 'Activer le mode sombre'}
            title={isDark ? 'Mode clair' : 'Mode sombre'}
          >
            <span aria-hidden="true">{isDark ? 'L' : 'N'}</span>
          </button>

          <button
            type="button"
            className="topbar-icon-btn"
            aria-label="Voir les notifications"
            title="Notifications"
          >
            <svg viewBox="0 0 24 24" width="16" height="16" fill="none" xmlns="http://www.w3.org/2000/svg">
              <path d="M12 4a5 5 0 0 0-5 5v2.6l-1.5 2.7a1 1 0 0 0 .87 1.5h11.26a1 1 0 0 0 .87-1.5L17 11.6V9a5 5 0 0 0-5-5z" stroke="currentColor" strokeWidth="1.8" />
              <path d="M10 18a2 2 0 0 0 4 0" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
            </svg>
            {notificationCount > 0 ? <span className="topbar-icon-badge">{notificationCount}</span> : null}
          </button>

          <div className="flex items-center gap-2 rounded-full px-1 py-1" style={{ background: 'var(--color-surface)' }}>
            <Link to="/profile" className="flex items-center gap-2 pl-1" aria-label="Voir mon profil">
              <div
                className="flex h-9 w-9 select-none items-center justify-center rounded-full text-sm font-bold"
                style={{ background: 'var(--color-primary-gradient)', color: '#ffffff' }}
              >
                {initial}
              </div>
              <div className="hidden flex-col leading-tight sm:flex">
                <span style={{ fontSize: '13px', fontWeight: 600, color: 'var(--color-text)' }}>
                  {user?.first_name ? `${user.first_name} ${user?.last_name ?? ''}`.trim() : user?.name || 'Utilisateur'}
                </span>
                <span className={`role-badge ${roleBadgeClass}`}>{roleLabel}</span>
              </div>
            </Link>
          </div>
        </div>
      </div>
    </header>
  );
}
