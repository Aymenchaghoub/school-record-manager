import apiClient from './apiClient';

export function createCrudService(endpoint) {
  return {
    async list(params = {}) {
      const response = await apiClient.get(endpoint, { params });
      return response.data;
    },

    async get(id) {
      const response = await apiClient.get(`${endpoint}/${id}`);
      return response.data;
    },

    async create(payload) {
      const response = await apiClient.post(endpoint, payload);
      return response.data;
    },

    async update(id, payload) {
      const response = await apiClient.put(`${endpoint}/${id}`, payload);
      return response.data;
    },

    async remove(id) {
      const response = await apiClient.delete(`${endpoint}/${id}`);
      return response.data;
    },
  };
}
