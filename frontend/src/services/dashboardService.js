import apiClient from './apiClient';
import { API_ENDPOINTS, ROLES } from '../utils/constants';

function resolveDashboardEndpoint(role) {
  return API_ENDPOINTS.dashboard[role] || API_ENDPOINTS.dashboard[ROLES.ADMIN];
}

async function getDashboardByRole(role) {
  const endpoint = resolveDashboardEndpoint(role);
  const response = await apiClient.get(endpoint);
  return response.data?.data || response.data;
}

export const dashboardService = {
  getDashboardByRole,
};
