import { API_ENDPOINTS } from '../utils/constants';
import { parseEntityResponse } from '../utils/response';
import apiClient from './apiClient';
import { requestWithFallback } from './requestWithFallback';

async function getCurrentUser() {
  const response = await requestWithFallback('get', API_ENDPOINTS.auth.meCandidates);
  const payload = parseEntityResponse(response.data);

  return payload?.user || payload;
}

async function login(credentials) {
  await apiClient.get(API_ENDPOINTS.auth.csrf);

  await requestWithFallback('post', API_ENDPOINTS.auth.loginCandidates, credentials);

  const user = await getCurrentUser();

  return {
    user,
  };
}

async function logout() {
  try {
    await requestWithFallback('post', API_ENDPOINTS.auth.logoutCandidates, {});
  } catch (error) {
    if (error?.response?.status !== 401) {
      throw error;
    }
  }
}

export const authService = {
  login,
  logout,
  getCurrentUser,
};
