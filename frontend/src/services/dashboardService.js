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

async function getStudentsPerClass() {
  const response = await apiClient.get('/api/dashboard/students-per-class');
  return response.data;
}

async function getAveragePerSubject() {
  const response = await apiClient.get('/api/dashboard/average-per-subject');
  return response.data;
}

async function getAbsencesPerMonth() {
  const response = await apiClient.get('/api/dashboard/absences-per-month');
  return response.data;
}

async function getGradeEvolution(studentId) {
  const params = studentId ? { student_id: studentId } : {};
  const response = await apiClient.get('/api/dashboard/grade-evolution', { params });
  return response.data;
}

async function getKpis() {
  const response = await apiClient.get('/api/dashboard/kpis');
  return response.data;
}

export const dashboardService = {
  getDashboardByRole,
  getStudentsPerClass,
  getAveragePerSubject,
  getAbsencesPerMonth,
  getGradeEvolution,
  getKpis,
};

export {
  getStudentsPerClass,
  getAveragePerSubject,
  getAbsencesPerMonth,
  getGradeEvolution,
  getKpis,
};
