import { API_ENDPOINTS, ROLES } from '../utils/constants';
import { createCrudService } from './crudService';

function resolveEndpoint(resourceKey, role) {
  const resourceMap = API_ENDPOINTS.resources[resourceKey];

  if (!resourceMap) {
    throw new Error(`Unknown resource key: ${resourceKey}`);
  }

  return (
    resourceMap[role] ||
    resourceMap.default ||
    resourceMap[ROLES.ADMIN] ||
    Object.values(resourceMap)[0]
  );
}

export function getResourceService(resourceKey, role) {
  const endpoint = resolveEndpoint(resourceKey, role);
  return createCrudService(endpoint);
}
