import { getResourceService } from './resourceServiceFactory';

export function createGradesService(role) {
  return getResourceService('grades', role);
}
