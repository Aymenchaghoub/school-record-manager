import { STORAGE_KEYS } from './constants';

export function getStoredAuthUser() {
  const serialized = localStorage.getItem(STORAGE_KEYS.AUTH_USER);

  if (!serialized) {
    return null;
  }

  try {
    return JSON.parse(serialized);
  } catch {
    return null;
  }
}

export function setStoredAuthUser(user) {
  if (!user) {
    clearStoredAuthUser();
    return;
  }

  localStorage.setItem(STORAGE_KEYS.AUTH_USER, JSON.stringify(user));
  localStorage.setItem(STORAGE_KEYS.AUTH_ROLE, user.role || '');
}

export function getStoredRole() {
  return localStorage.getItem(STORAGE_KEYS.AUTH_ROLE) || '';
}

export function clearStoredAuthUser() {
  localStorage.removeItem(STORAGE_KEYS.AUTH_USER);
  localStorage.removeItem(STORAGE_KEYS.AUTH_ROLE);
}
