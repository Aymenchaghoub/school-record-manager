import { createContext, useCallback, useEffect, useMemo, useState } from 'react';
import { authService } from '../services/authService';

export const AuthContext = createContext(null);

export function AuthProvider({ children }) {
  const [user, setUser] = useState(null);
  const [isLoading, setIsLoading] = useState(true);

  const bootstrap = useCallback(async () => {
    setIsLoading(true);

    try {
      const currentUser = await authService.getCurrentUser();
      setUser(currentUser || null);
    } catch (error) {
      if (error?.response?.status !== 401) {
        console.error('Failed to bootstrap authenticated user', error);
      }

      setUser(null);
    } finally {
      setIsLoading(false);
    }
  }, []);

  useEffect(() => {
    bootstrap();
  }, [bootstrap]);

  const login = useCallback(async (credentials) => {
    const result = await authService.login(credentials);
    setUser(result.user || null);
    return result.user;
  }, []);

  const logout = useCallback(async () => {
    await authService.logout();
    setUser(null);
  }, []);

  useEffect(() => {
    const handleUnauthorized = () => {
      setUser(null);
    };

    window.addEventListener('auth:unauthorized', handleUnauthorized);
    return () => window.removeEventListener('auth:unauthorized', handleUnauthorized);
  }, []);

  const value = useMemo(
    () => ({
      user,
      isLoading,
      isAuthenticated: Boolean(user),
      login,
      refreshUser: bootstrap,
      logout,
    }),
    [user, isLoading, login, bootstrap, logout]
  );

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}
