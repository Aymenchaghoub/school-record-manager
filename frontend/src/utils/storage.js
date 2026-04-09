import { STORAGE_KEYS } from './constants';

export function getAuthToken() {
  return localStorage.getItem(STORAGE_KEYS.AUTH_TOKEN) || '';
}

export function setAuthToken(token) {
  if (!token) {
    return;
  }

  localStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, token);
}

export function clearAuthToken() {
  localStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN);
}
