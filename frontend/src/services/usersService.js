import { getResourceService } from './resourceServiceFactory';

export function createUsersService(role) {
  return getResourceService('users', role);
}
