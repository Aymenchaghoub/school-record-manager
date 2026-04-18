import { getResourceService } from './resourceServiceFactory';

export function createSubjectsService(role) {
  return getResourceService('subjects', role);
}
