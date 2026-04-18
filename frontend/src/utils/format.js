import { ROLE_LABELS } from './constants';

export function formatDate(value) {
  if (!value) {
    return '-';
  }

  try {
    return new Date(value).toLocaleDateString('fr-FR');
  } catch {
    return String(value);
  }
}

export function formatDateTime(value) {
  if (!value) {
    return '-';
  }

  try {
    return new Date(value).toLocaleString('fr-FR');
  } catch {
    return String(value);
  }
}

export function formatRole(role) {
  return ROLE_LABELS[role] || role || '-';
}

export function toBoolean(value) {
  if (typeof value === 'boolean') {
    return value;
  }

  if (typeof value === 'number') {
    return value === 1;
  }

  if (typeof value === 'string') {
    return ['1', 'true', 'yes', 'on'].includes(value.toLowerCase());
  }

  return false;
}
