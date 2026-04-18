import { getResourceService } from './resourceServiceFactory';

export function createEventsService(role) {
  return getResourceService('events', role);
}
