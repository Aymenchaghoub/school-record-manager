export const APP_NAME = 'School Record Manager';

export const ROLES = {
  ADMIN: 'admin',
  TEACHER: 'teacher',
  STUDENT: 'student',
  PARENT: 'parent',
};

export const STORAGE_KEYS = {
  AUTH_USER: 'srm.auth.user',
  AUTH_ROLE: 'srm.auth.role',
};

export const ROLE_LABELS = {
  [ROLES.ADMIN]: 'Administrateur',
  [ROLES.TEACHER]: 'Enseignant',
  [ROLES.STUDENT]: 'Etudiant',
  [ROLES.PARENT]: 'Parent',
};

export const NAV_ITEMS_BY_ROLE = {
  [ROLES.ADMIN]: [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/users', label: 'Utilisateurs' },
    { to: '/classes', label: 'Classes' },
    { to: '/subjects', label: 'Matieres' },
    { to: '/grades', label: 'Notes' },
    { to: '/absences', label: 'Absences' },
    { to: '/report-cards', label: 'Bulletins' },
    { to: '/events', label: 'Evenements' },
  ],
  [ROLES.TEACHER]: [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/grades', label: 'Notes' },
    { to: '/absences', label: 'Absences' },
  ],
  [ROLES.STUDENT]: [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/grades', label: 'Mes notes' },
    { to: '/absences', label: 'Mes absences' },
  ],
  [ROLES.PARENT]: [
    { to: '/dashboard', label: 'Dashboard' },
    { to: '/grades', label: 'Notes enfants' },
    { to: '/absences', label: 'Absences enfants' },
  ],
};

export const API_ENDPOINTS = {
  auth: {
    csrf: '/sanctum/csrf-cookie',
    loginCandidates: ['/api/login', '/login'],
    meCandidates: ['/api/user'],
    logoutCandidates: ['/api/logout', '/logout'],
  },
  dashboard: {
    [ROLES.ADMIN]: '/api/admin/dashboard',
    [ROLES.TEACHER]: '/api/teacher/dashboard',
    [ROLES.STUDENT]: '/api/student/dashboard',
    [ROLES.PARENT]: '/api/parent/dashboard',
  },
  resources: {
    users: {
      [ROLES.ADMIN]: '/api/admin/users',
    },
    classes: {
      [ROLES.ADMIN]: '/api/admin/classes',
    },
    subjects: {
      [ROLES.ADMIN]: '/api/admin/subjects',
    },
    grades: {
      [ROLES.ADMIN]: '/api/admin/grades',
      [ROLES.TEACHER]: '/api/teacher/grades',
      [ROLES.STUDENT]: '/api/student/grades',
      [ROLES.PARENT]: '/api/parent/children/grades',
    },
    absences: {
      [ROLES.ADMIN]: '/api/admin/absences',
      [ROLES.TEACHER]: '/api/teacher/absences',
      [ROLES.STUDENT]: '/api/student/absences',
      [ROLES.PARENT]: '/api/parent/children/absences',
    },
    reportCards: {
      [ROLES.ADMIN]: '/api/admin/report-cards',
    },
    events: {
      [ROLES.ADMIN]: '/api/admin/events',
    },
  },
};

export const FALLBACK_PAGINATION = {
  currentPage: 1,
  totalPages: 1,
  total: 0,
};
