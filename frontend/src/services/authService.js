import { API_ENDPOINTS } from '../utils/constants';
import { clearAuthToken, setAuthToken } from '../utils/storage';
import { parseEntityResponse } from '../utils/response';
import apiClient from './apiClient';
import { requestWithFallback } from './requestWithFallback';

async function getCurrentUser() {
  const response = await requestWithFallback('get', API_ENDPOINTS.auth.meCandidates);
  const payload = parseEntityResponse(response.data);

  return payload?.user || payload;
}

async function login(credentials) {
  try {
    await apiClient.get(API_ENDPOINTS.auth.csrf);
  } catch {
    // CSRF endpoint may not exist depending on backend auth strategy.
  }

  const response = await requestWithFallback(
    'post',
    API_ENDPOINTS.auth.loginCandidates,
    credentials
  );

  const payload = parseEntityResponse(response.data);
  const token = payload?.token || payload?.access_token || '';

  if (token) {
    setAuthToken(token);
  }

  const user = payload?.user || (await getCurrentUser());

  return {
    user,
    token,
  };
}

async function logout() {
  try {
    await requestWithFallback('post', API_ENDPOINTS.auth.logoutCandidates, {});
  } catch {
    // Clearing local auth state is enough if backend session is already closed.
  } finally {
    clearAuthToken();
  }
}

export const authService = {
  login,
  logout,
  getCurrentUser,
};
