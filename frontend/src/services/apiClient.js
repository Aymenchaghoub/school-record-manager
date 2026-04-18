import axios from 'axios';

axios.defaults.withCredentials = true;
axios.defaults.withXSRFToken = true;

const DEFAULT_API_BASE_URL = 'http://localhost:8000';
const SANCTUM_CSRF_PATH = '/sanctum/csrf-cookie';

const configuredBaseUrl = String(import.meta.env.VITE_API_URL || '').trim();
const normalizedBaseUrl = configuredBaseUrl || DEFAULT_API_BASE_URL;

const apiClient = axios.create({
  baseURL: normalizedBaseUrl,
  withCredentials: true,
  withXSRFToken: true,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
});

let csrfRefreshPromise = null;

export async function ensureCsrfCookie() {
  if (!csrfRefreshPromise) {
    csrfRefreshPromise = apiClient
      .get(SANCTUM_CSRF_PATH, { __skipCsrfRefresh: true })
      .finally(() => {
        csrfRefreshPromise = null;
      });
  }

  return csrfRefreshPromise;
}

apiClient.interceptors.request.use(async (config) => {
  const method = String(config?.method || 'get').toLowerCase();
  const isMutation = ['post', 'put', 'patch', 'delete'].includes(method);
  const shouldSkipRefresh = Boolean(config?.__skipCsrfRefresh);
  const requestUrl = String(config?.url || '');
  const isCsrfRequest = requestUrl.includes(SANCTUM_CSRF_PATH);

  if (isMutation && !shouldSkipRefresh && !isCsrfRequest) {
    await ensureCsrfCookie();
  }

  return config;
});

apiClient.interceptors.response.use(
  (response) => response,
  (error) => {
    const status = error?.response?.status;
    const message =
      error?.response?.data?.message ||
      'Une erreur inattendue est survenue';

    if (status === 401) {
      window.dispatchEvent(new Event('unauthorized'));
      window.dispatchEvent(new CustomEvent('auth:unauthorized'));
    } else if (status === 403) {
      console.error('[API] Acces refuse:', error?.config?.url);
    } else if (status === 404) {
      console.error('[API] Endpoint introuvable:', error?.config?.url);
    } else if (status >= 500) {
      console.error('[API] Erreur serveur:', message);
    }

    return Promise.reject({ status, message, original: error });
  }
);

export default apiClient;
