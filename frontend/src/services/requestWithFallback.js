import apiClient from './apiClient';

function sanitizeEndpoints(endpoints = []) {
  return [...new Set(endpoints.filter(Boolean))];
}

export async function requestWithFallback(method, endpoints, payload, config) {
  const cleanEndpoints = sanitizeEndpoints(endpoints);
  let lastError;

  for (const endpoint of cleanEndpoints) {
    try {
      if (method === 'get' || method === 'delete') {
        return await apiClient[method](endpoint, config);
      }

      return await apiClient[method](endpoint, payload, config);
    } catch (error) {
      if (error?.response?.status && error.response.status !== 404) {
        throw error;
      }

      lastError = error;
    }
  }

  throw lastError || new Error('No endpoint available');
}
