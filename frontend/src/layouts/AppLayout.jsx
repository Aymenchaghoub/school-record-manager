import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';
import { Sidebar } from './Sidebar';
import { TopBar } from './TopBar';

export function AppLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { user, role, logout } = useAuth();

  return (
    <div className="app-shell min-h-screen">
      <Sidebar
        role={role || user?.role}
        isOpen={sidebarOpen}
        onNavigate={() => setSidebarOpen(false)}
        onLogout={logout}
      />

      <div className="md:pl-56">
        <TopBar
          user={user}
          onToggleSidebar={() => setSidebarOpen((open) => !open)}
        />

        <main className="px-4 py-6 lg:px-8" style={{ color: 'var(--fg)' }}>
          <Outlet />
        </main>
      </div>

      {sidebarOpen ? (
        <button
          type="button"
          aria-label="Fermer le panneau lateral"
          onClick={() => setSidebarOpen(false)}
          className="fixed inset-0 z-30 md:hidden"
          style={{ background: 'rgba(15, 23, 42, 0.45)' }}
        />
      ) : null}
    </div>
  );
}
