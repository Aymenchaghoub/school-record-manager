import { useCallback, useState } from 'react';

export function useAsyncAction(asyncFn) {
  const [isPending, setIsPending] = useState(false);
  const [error, setError] = useState('');

  const execute = useCallback(
    async (...args) => {
      setIsPending(true);
      setError('');

      try {
        return await asyncFn(...args);
      } catch (err) {
        setError(err?.response?.data?.message || err?.message || 'Unexpected error');
        throw err;
      } finally {
        setIsPending(false);
      }
    },
    [asyncFn]
  );

  return {
    execute,
    isPending,
    error,
    clearError: () => setError(''),
  };
}
