import { Link, useLocation } from 'react-router-dom';
import { APP_NAME, NAV_ITEMS_BY_ROLE } from '../utils/constants';

const sectionTitleByKey = {
  general: 'GENERAL',
  administration: 'ADMINISTRATION',
  pedagogie: 'PEDAGOGIE',
  espace: 'ESPACE',
};

function getSectionKey(pathname) {
  if (pathname === '/dashboard') {
    return 'general';
  }

  if (['/users', '/classes', '/subjects'].includes(pathname)) {
    return 'administration';
  }

  if (['/grades', '/absences', '/report-cards', '/events'].includes(pathname)) {
    return 'pedagogie';
  }

  return 'espace';
}

export function Sidebar({ role, isOpen, onNavigate, onLogout }) {
  const items = NAV_ITEMS_BY_ROLE[role] || [];
  const location = useLocation();

  const groupedItems = ['general', 'administration', 'pedagogie', 'espace'].reduce((acc, key) => {
    acc[key] = [];
    return acc;
  }, {});

  items.forEach((item) => {
    const key = getSectionKey(item.to);
    groupedItems[key].push(item);
  });

  const isItemActive = (path) => {
    if (location.pathname === path) {
      return true;
    }

    return location.pathname.startsWith(`${path}/`);
  };

  return (
    <aside
      className={`app-sidebar fixed inset-y-0 left-0 z-40 flex flex-col p-4 transition-transform duration-300 ease-in-out md:translate-x-0 ${
        isOpen ? 'translate-x-0' : '-translate-x-full'
      }`}
    >
      <div className="mb-5 flex items-center gap-2">
        <div
          className="flex h-8 w-8 items-center justify-center rounded-full text-white"
          style={{ background: 'var(--color-primary-gradient)' }}
          aria-hidden="true"
        >
          S
        </div>
        <h2
          className="text-lg font-bold"
          style={{
            background: 'var(--color-primary-gradient)',
            WebkitBackgroundClip: 'text',
            backgroundClip: 'text',
            color: 'transparent',
          }}
        >
          {APP_NAME}
        </h2>
      </div>

      <nav className="flex-1 space-y-4 overflow-y-auto pr-1">
        {Object.entries(groupedItems).map(([sectionKey, sectionItems]) => {
          return (
            <div key={sectionKey}>
              <p className="sidebar-section-title">{sectionTitleByKey[sectionKey]}</p>
              {sectionItems.length > 0 ? (
                <div className="space-y-1">
                  {sectionItems.map((item) => {
                    const active = isItemActive(item.to);

                    return (
                      <Link
                        key={item.to}
                        to={item.to}
                        onClick={onNavigate}
                        className={`sidebar-link ${active ? 'sidebar-link-active' : ''}`}
                      >
                        {item.label}
                      </Link>
                    );
                  })}
                </div>
              ) : null}
            </div>
          );
        })}
      </nav>

      <div className="sidebar-footer mt-3 space-y-2">
        <Link to="/profile" onClick={onNavigate} className="sidebar-footer-action">
          <span>Mon profil</span>
          <span aria-hidden="true">{'>'}</span>
        </Link>
        <button
          type="button"
          onClick={onLogout}
          className="sidebar-footer-action"
          style={{ color: 'var(--color-danger)' }}
        >
          <span>Deconnexion</span>
          <span aria-hidden="true">x</span>
        </button>
      </div>
    </aside>
  );
}
