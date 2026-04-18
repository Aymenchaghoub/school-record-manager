import { createContext, useCallback, useContext, useEffect, useMemo, useRef, useState } from 'react';

const NOOP = () => {};

const LoadingContext = createContext(null);

let globalLoadingHandlers = {
  showLoader: NOOP,
  hideLoader: NOOP,
};

export function getLoadingHandlers() {
  return globalLoadingHandlers;
}

export function LoadingProvider({ children }) {
  const pendingCountRef = useRef(0);
  const [isLoading, setIsLoading] = useState(false);

  const showLoader = useCallback(() => {
    pendingCountRef.current += 1;
    setIsLoading(true);
  }, []);

  const hideLoader = useCallback(() => {
    pendingCountRef.current = Math.max(0, pendingCountRef.current - 1);
    setIsLoading(pendingCountRef.current > 0);
  }, []);

  useEffect(() => {
    globalLoadingHandlers = { showLoader, hideLoader };

    return () => {
      globalLoadingHandlers = { showLoader: NOOP, hideLoader: NOOP };
    };
  }, [showLoader, hideLoader]);

  const value = useMemo(
    () => ({
      isLoading,
      showLoader,
      hideLoader,
    }),
    [isLoading, showLoader, hideLoader]
  );

  return <LoadingContext.Provider value={value}>{children}</LoadingContext.Provider>;
}

export function useLoading() {
  const context = useContext(LoadingContext);

  if (!context) {
    throw new Error('useLoading must be used inside LoadingProvider');
  }

  return context;
}
