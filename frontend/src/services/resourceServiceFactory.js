import { API_ENDPOINTS } from '../utils/constants';
import { createCrudService } from './crudService';

function resolveEndpoint(resourceKey, role) {
  const resourceMap = API_ENDPOINTS.resources[resourceKey];

  if (!resourceMap) {
    throw new Error(`Unknown resource key: ${resourceKey}`);
  }

  const endpoint = resourceMap[role] || resourceMap.default;

  if (!endpoint) {
    throw new Error(`No endpoint configured for resource ${resourceKey} and role ${role}`);
  }

  return endpoint;
}

export function getResourceService(resourceKey, role) {
  const endpoint = resolveEndpoint(resourceKey, role);
  return createCrudService(endpoint);
}
