import { useEffect, useRef, useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import useNotifications from '../hooks/useNotifications';
import { useTheme } from '../context/ThemeContext';
import FR from '../i18n/fr';

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

function getInitials(user) {
  const first = String(user?.first_name || '').trim();
  const last = String(user?.last_name || '').trim();

  if (first || last) {
    return `${first.charAt(0)}${last.charAt(0)}`.trim().toUpperCase() || '?';
  }

  const nameParts = String(user?.name || '')
    .trim()
    .split(/\s+/)
    .filter(Boolean);

  if (nameParts.length === 0) {
    return '?';
  }

  if (nameParts.length === 1) {
    return nameParts[0].slice(0, 2).toUpperCase();
  }

  return `${nameParts[0].charAt(0)}${nameParts[1].charAt(0)}`.toUpperCase();
}

export function TopBar({ user, onToggleSidebar }) {
  const { isDark, toggleTheme } = useTheme();
  const location = useLocation();
  const { notifications, unreadCount, markAllRead } = useNotifications();
  const [showNotifDropdown, setShowNotifDropdown] = useState(false);
  const notifContainerRef = useRef(null);

  const currentSegment = location.pathname.split('/').filter(Boolean).slice(-1)[0] || 'dashboard';
  const currentLabel = routeLabelMap[currentSegment] || 'Application';

  const role = user?.role || 'student';
  const roleBadgeClass = roleBadgeClassMap[role] || 'role-badge-student';
  const roleLabel = roleLabelMap[role] || String(role || '-').toUpperCase();
  const initials = getInitials(user);

  useEffect(() => {
    const handleOutsideClick = (event) => {
      if (!notifContainerRef.current) {
        return;
      }

      if (!notifContainerRef.current.contains(event.target)) {
        setShowNotifDropdown(false);
      }
    };

    document.addEventListener('mousedown', handleOutsideClick);
    return () => {
      document.removeEventListener('mousedown', handleOutsideClick);
    };
  }, []);

  return (
    <header className="app-topbar sticky top-0 z-30 border-b px-4 py-3 backdrop-blur lg:px-8">
      <div className="flex items-center gap-3">
        <button
          type="button"
          onClick={onToggleSidebar}
          className="inline-flex items-center gap-2 rounded-md border px-3 py-1 text-sm font-medium transition-colors duration-150 md:hidden"
          style={{ borderColor: 'var(--color-border)', color: 'var(--color-text)' }}
          aria-label={FR.topBar.menuLabel}
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
            placeholder={FR.topBar.searchPlaceholder}
            aria-label="Recherche globale"
          />
        </div>

        <div className="ml-auto flex items-center gap-3">
          <button
            type="button"
            onClick={toggleTheme}
            className="topbar-icon-btn"
            aria-label={isDark ? FR.topBar.themeToLight : FR.topBar.themeToDark}
            title={isDark ? FR.topBar.modeLight : FR.topBar.modeDark}
          >
            {isDark ? (
              <svg
                viewBox="0 0 24 24"
                width="20"
                height="20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
              >
                <circle cx="12" cy="12" r="4" stroke="currentColor" strokeWidth="1.8" />
                <path d="M12 2V5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                <path d="M12 19V22" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                <path d="M2 12H5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                <path d="M19 12H22" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                <path d="M4.9 4.9L7 7" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                <path d="M17 17L19.1 19.1" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                <path d="M17 7L19.1 4.9" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
                <path d="M4.9 19.1L7 17" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
              </svg>
            ) : (
              <svg
                viewBox="0 0 24 24"
                width="20"
                height="20"
                fill="none"
                xmlns="http://www.w3.org/2000/svg"
                aria-hidden="true"
              >
                <path
                  d="M20.3 14.7A8.7 8.7 0 1 1 9.3 3.7a7 7 0 1 0 11 11z"
                  stroke="currentColor"
                  strokeWidth="1.8"
                  strokeLinecap="round"
                  strokeLinejoin="round"
                />
              </svg>
            )}
          </button>

          <div ref={notifContainerRef} style={{ position: 'relative' }}>
            <button
              type="button"
              className="topbar-icon-btn"
              aria-label={FR.topBar.notificationsLabel}
              title="Notifications"
              onClick={() => setShowNotifDropdown((previous) => !previous)}
            >
              <svg viewBox="0 0 24 24" width="20" height="20" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path d="M12 4a5 5 0 0 0-5 5v2.6l-1.5 2.7a1 1 0 0 0 .87 1.5h11.26a1 1 0 0 0 .87-1.5L17 11.6V9a5 5 0 0 0-5-5z" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
                <path d="M10 18a2 2 0 0 0 4 0" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
              </svg>
              {unreadCount > 0 ? <span className="topbar-icon-badge">{unreadCount}</span> : null}
            </button>

            {showNotifDropdown ? (
              <div
                style={{
                  position: 'absolute',
                  top: '48px',
                  right: 0,
                  background: 'var(--color-surface)',
                  border: '1px solid var(--color-border)',
                  borderRadius: '12px',
                  width: '320px',
                  boxShadow: 'var(--shadow-card)',
                  zIndex: 100,
                }}
              >
                <div
                  style={{
                    padding: '12px 16px',
                    borderBottom: '1px solid var(--color-border)',
                    display: 'flex',
                    justifyContent: 'space-between',
                    alignItems: 'center',
                  }}
                >
                  <span style={{ fontWeight: 600, fontSize: '14px' }}>Notifications</span>
                  {unreadCount > 0 ? (
                    <button
                      onClick={markAllRead}
                      style={{
                        fontSize: '12px',
                        color: 'var(--color-primary)',
                        background: 'none',
                        border: 'none',
                        cursor: 'pointer',
                      }}
                    >
                      Tout marquer comme lu
                    </button>
                  ) : null}
                </div>

                {notifications.length === 0 ? (
                  <div style={{ padding: '24px', textAlign: 'center', color: 'var(--color-muted)', fontSize: '13px' }}>
                    Aucune notification
                  </div>
                ) : (
                  notifications.map((notification) => (
                    <div
                      key={notification.id}
                      style={{
                        padding: '12px 16px',
                        borderBottom: '1px solid var(--color-border)',
                        background: notification.read ? 'transparent' : '#F5F3FF',
                        fontSize: '13px',
                      }}
                    >
                      <p style={{ fontWeight: 500, margin: '0 0 4px' }}>{notification.message}</p>
                      <p style={{ color: 'var(--color-muted)', margin: 0 }}>
                        {notification.subject || 'Information'} - {notification.date || ''}
                      </p>
                    </div>
                  ))
                )}
              </div>
            ) : null}
          </div>

          <div className="flex items-center gap-2 rounded-full px-1 py-1" style={{ background: 'var(--color-surface)' }}>
            <Link to="/profile" className="flex items-center gap-2 pl-1" aria-label={FR.topBar.profileLabel}>
              <div
                className="flex h-9 w-9 select-none items-center justify-center rounded-full text-sm font-bold"
                style={{
                  width: '36px',
                  height: '36px',
                  background: 'linear-gradient(135deg, #E040A0, #A855F7)',
                  color: '#ffffff',
                }}
              >
                {initials}
              </div>
              <div className="hidden flex-col leading-tight sm:flex">
                <span style={{ fontSize: '13px', fontWeight: 600, color: 'var(--color-text)' }}>
                  {user?.first_name
                    ? `${user.first_name} ${user?.last_name ?? ''}`.trim()
                    : user?.name || FR.topBar.profileFallbackName}
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
