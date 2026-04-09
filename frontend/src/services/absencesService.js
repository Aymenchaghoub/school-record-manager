import { getResourceService } from './resourceServiceFactory';

export function createAbsencesService(role) {
  return getResourceService('absences', role);
}
