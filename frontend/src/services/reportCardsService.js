import { getResourceService } from './resourceServiceFactory';

export function createReportCardsService(role) {
  return getResourceService('reportCards', role);
}
