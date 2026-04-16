import { API_ENDPOINTS } from '../utils/constants';
import { parseEntityResponse } from '../utils/response';
import apiClient from './apiClient';
import { ensureCsrfCookie } from './apiClient';
import { requestWithFallback } from './requestWithFallback';

async function getCurrentUser() {
  const response = await requestWithFallback('get', API_ENDPOINTS.auth.meCandidates);
  const payload = parseEntityResponse(response.data);

  return payload?.user || payload;
}

async function login(credentials) {
  await ensureCsrfCookie();

  await requestWithFallback('post', API_ENDPOINTS.auth.loginCandidates, credentials);

  const user = await getCurrentUser();

  return {
    user,
  };
}

async function logout() {
  try {
    await ensureCsrfCookie();
    await requestWithFallback('post', API_ENDPOINTS.auth.logoutCandidates, {});
  } catch (error) {
    const status = error?.status ?? error?.response?.status;
    if (status !== 401) {
      throw error;
    }
  }
}

export async function updateProfile(data) {
  await ensureCsrfCookie();
  const response = await apiClient.put('/api/v1/profile', data);
  return response.data;
}

export const authService = {
  login,
  logout,
  getCurrentUser,
  updateProfile,
};
