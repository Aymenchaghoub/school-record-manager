import { API_ENDPOINTS } from '../utils/constants';
import toast from 'react-hot-toast';
import { getLoadingHandlers } from '../context/LoadingContext';
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

async function withGlobalLoader(request) {
  const { showLoader, hideLoader } = getLoadingHandlers();
  showLoader();

  try {
    return await request();
  } catch (error) {
    const statusCode = error?.response?.status;

    if (statusCode === 401 || statusCode === 403) {
      toast.error('Access denied');
    } else {
      toast.error(error?.response?.data?.message || 'An error occurred');
    }

    throw error;
  } finally {
    hideLoader();
  }
}

export function getResourceService(resourceKey, role) {
  const endpoint = resolveEndpoint(resourceKey, role);
  const baseService = createCrudService(endpoint);

  return {
    list(params = {}) {
      return withGlobalLoader(() => baseService.list(params));
    },

    get(id) {
      return withGlobalLoader(() => baseService.get(id));
    },

    create(payload) {
      return withGlobalLoader(async () => {
        const result = await baseService.create(payload);
        toast.success('Created successfully');
        return result;
      });
    },

    update(id, payload) {
      return withGlobalLoader(async () => {
        const result = await baseService.update(id, payload);
        toast.success('Updated successfully');
        return result;
      });
    },

    remove(id) {
      return withGlobalLoader(async () => {
        const result = await baseService.remove(id);
        toast.success('Deleted successfully');
        return result;
      });
    },
  };
}
