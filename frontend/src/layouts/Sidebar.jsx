import { Link, useLocation } from 'react-router-dom';
import { APP_NAME, NAV_ITEMS_BY_ROLE } from '../utils/constants';

export function Sidebar({ role, isOpen, onNavigate }) {
  const items = NAV_ITEMS_BY_ROLE[role] || [];
  const location = useLocation();

  const isItemActive = (path) => {
    if (location.pathname === path) {
      return true;
    }

    return location.pathname.startsWith(`${path}/`);
  };

  return (
    <aside
      className={`app-sidebar fixed inset-y-0 left-0 z-40 w-72 border-r p-5 shadow-soft transition-transform duration-300 ease-in-out md:translate-x-0 ${
        isOpen ? 'translate-x-0' : '-translate-x-full'
      }`}
    >
      <div className="mb-8">
        <p className="theme-muted text-xs font-semibold uppercase tracking-[0.2em]">SchoolSphere</p>
        <h2 className="mt-1 text-xl font-black" style={{ color: 'var(--fg)' }}>
          {APP_NAME}
        </h2>
      </div>

      <nav className="space-y-1">
        {items.map((item) => {
          const active = isItemActive(item.to);

          return (
            <Link
              key={item.to}
              to={item.to}
              onClick={onNavigate}
              className={`sidebar-link block rounded-xl border-l-4 px-3 py-2 text-sm font-medium ${
                active ? 'sidebar-link-active' : ''
              }`}
            >
              {item.label}
            </Link>
          );
        })}
      </nav>
    </aside>
  );
}
