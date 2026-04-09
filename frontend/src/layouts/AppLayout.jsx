import { useState } from 'react';
import { Outlet } from 'react-router-dom';
import { useAuth } from '../hooks/useAuth';
import { Sidebar } from './Sidebar';
import { TopBar } from './TopBar';

export function AppLayout() {
  const [sidebarOpen, setSidebarOpen] = useState(false);
  const { user, role, logout } = useAuth();

  return (
    <div className="min-h-screen bg-slate-100">
      <Sidebar
        role={role || user?.role}
        isOpen={sidebarOpen}
        onNavigate={() => setSidebarOpen(false)}
      />

      <div className="lg:pl-72">
        <TopBar
          user={user}
          onLogout={logout}
          onToggleSidebar={() => setSidebarOpen((open) => !open)}
        />

        <main className="px-4 py-6 lg:px-8">
          <Outlet />
        </main>
      </div>

      {sidebarOpen ? (
        <button
          type="button"
          aria-label="Close sidebar overlay"
          onClick={() => setSidebarOpen(false)}
          className="fixed inset-0 z-30 bg-slate-900/40 lg:hidden"
        />
      ) : null}
    </div>
  );
}
