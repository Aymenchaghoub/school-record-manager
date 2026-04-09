import { getResourceService } from './resourceServiceFactory';

export function createClassesService(role) {
  return getResourceService('classes', role);
}
