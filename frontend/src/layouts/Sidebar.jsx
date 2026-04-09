import { NavLink } from 'react-router-dom';
import { APP_NAME, NAV_ITEMS_BY_ROLE } from '../utils/constants';

export function Sidebar({ role, isOpen, onNavigate }) {
  const items = NAV_ITEMS_BY_ROLE[role] || [];

  return (
    <aside
      className={`fixed inset-y-0 left-0 z-40 w-72 border-r border-slate-200 bg-white p-5 shadow-soft transition-transform lg:translate-x-0 ${
        isOpen ? 'translate-x-0' : '-translate-x-full'
      }`}
    >
      <div className="mb-8">
        <p className="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">SchoolSphere</p>
        <h2 className="mt-1 text-xl font-black text-slate-900">{APP_NAME}</h2>
      </div>

      <nav className="space-y-1">
        {items.map((item) => (
          <NavLink
            key={item.to}
            to={item.to}
            onClick={onNavigate}
            className={({ isActive }) =>
              `block rounded-xl px-3 py-2 text-sm font-medium transition ${
                isActive
                  ? 'bg-cyan-100 text-cyan-800'
                  : 'text-slate-600 hover:bg-slate-100 hover:text-slate-900'
              }`
            }
          >
            {item.label}
          </NavLink>
        ))}
      </nav>
    </aside>
  );
}
