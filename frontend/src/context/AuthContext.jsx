import { createContext, useCallback, useEffect, useMemo, useState } from 'react';
import { authService } from '../services/authService';
import {
  clearStoredAuthUser,
  getStoredAuthUser,
  getStoredRole,
  setStoredAuthUser,
} from '../utils/storage';

export const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const initialUser = getStoredAuthUser();

  const [user, setUser] = useState(initialUser);
  const [role, setRole] = useState(initialUser?.role || getStoredRole() || null);
  const [isLoading, setIsLoading] = useState(true);

  const applyUser = useCallback((nextUser) => {
    setUser(nextUser || null);

    const nextRole = nextUser?.role || null;
    setRole(nextRole);

    if (nextUser) {
      setStoredAuthUser(nextUser);
    } else {
      clearStoredAuthUser();
    }
  }, []);

  const bootstrap = useCallback(async () => {
    setIsLoading(true);

    try {
      const currentUser = await authService.getCurrentUser();
      applyUser(currentUser || null);
    } catch (error) {
      const status = error?.status ?? error?.response?.status;
      if (status !== 401) {
        console.error('Failed to bootstrap authenticated user', error);
      }

      applyUser(null);
    } finally {
      setIsLoading(false);
    }
  }, [applyUser]);

  useEffect(() => {
    bootstrap();
  }, [bootstrap]);

  const login = useCallback(async (credentials) => {
    const result = await authService.login(credentials);
    applyUser(result.user || null);
    return result.user;
  }, [applyUser]);

  const logout = useCallback(async () => {
    await authService.logout();
    applyUser(null);
  }, [applyUser]);

  useEffect(() => {
    const handleUnauthorized = () => {
      applyUser(null);
    };

    window.addEventListener('unauthorized', handleUnauthorized);
    window.addEventListener('auth:unauthorized', handleUnauthorized);
    return () => {
      window.removeEventListener('unauthorized', handleUnauthorized);
      window.removeEventListener('auth:unauthorized', handleUnauthorized);
    };
  }, [applyUser]);

  const value = useMemo(
    () => ({
      user,
      role,
      isLoading,
      isAuthenticated: Boolean(user),
      login,
      refreshUser: bootstrap,
      logout,
    }),
    [user, role, isLoading, login, bootstrap, logout]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
