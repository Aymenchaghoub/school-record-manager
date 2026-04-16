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
    { to: '/dashboard', label: 'Tableau de bord' },
    { to: '/users', label: 'Utilisateurs' },
    { to: '/classes', label: 'Classes' },
    { to: '/subjects', label: 'Matieres' },
    { to: '/grades', label: 'Notes' },
    { to: '/absences', label: 'Absences' },
    { to: '/report-cards', label: 'Bulletins' },
    { to: '/events', label: 'Evenements' },
  ],
  [ROLES.TEACHER]: [
    { to: '/dashboard', label: 'Tableau de bord' },
    { to: '/grades', label: 'Notes' },
    { to: '/absences', label: 'Absences' },
  ],
  [ROLES.STUDENT]: [
    { to: '/dashboard', label: 'Tableau de bord' },
    { to: '/grades', label: 'Mes notes' },
    { to: '/absences', label: 'Mes absences' },
  ],
  [ROLES.PARENT]: [
    { to: '/dashboard', label: 'Tableau de bord' },
    { to: '/grades', label: 'Notes enfants' },
    { to: '/absences', label: 'Absences enfants' },
  ],
};

export const API_ENDPOINTS = {
  auth: {
    csrf: '/sanctum/csrf-cookie',
    loginCandidates: ['/api/v1/login', '/api/login', '/login'],
    meCandidates: ['/api/v1/user', '/api/user'],
    logoutCandidates: ['/api/v1/logout', '/api/logout', '/logout'],
  },
  dashboard: {
    [ROLES.ADMIN]: '/api/v1/admin/dashboard',
    [ROLES.TEACHER]: '/api/v1/teacher/dashboard',
    [ROLES.STUDENT]: '/api/v1/student/dashboard',
    [ROLES.PARENT]: '/api/v1/parent/dashboard',
  },
  resources: {
    users: {
      [ROLES.ADMIN]: '/api/v1/admin/users',
    },
    classes: {
      [ROLES.ADMIN]: '/api/v1/admin/classes',
      [ROLES.TEACHER]: '/api/v1/teacher/classes',
    },
    subjects: {
      [ROLES.ADMIN]: '/api/v1/admin/subjects',
      [ROLES.TEACHER]: '/api/v1/teacher/subjects',
    },
    grades: {
      [ROLES.ADMIN]: '/api/v1/admin/grades',
      [ROLES.TEACHER]: '/api/v1/teacher/grades',
      [ROLES.STUDENT]: '/api/v1/student/grades',
      [ROLES.PARENT]: '/api/v1/parent/children/grades',
    },
    absences: {
      [ROLES.ADMIN]: '/api/v1/admin/absences',
      [ROLES.TEACHER]: '/api/v1/teacher/absences',
      [ROLES.STUDENT]: '/api/v1/student/absences',
      [ROLES.PARENT]: '/api/v1/parent/children/absences',
    },
    reportCards: {
      [ROLES.ADMIN]: '/api/v1/admin/report-cards',
    },
    events: {
      [ROLES.ADMIN]: '/api/v1/admin/events',
      [ROLES.TEACHER]: '/api/v1/teacher/events',
      [ROLES.PARENT]: '/api/v1/parent/events',
    },
  },
};

export const FALLBACK_PAGINATION = {
  currentPage: 1,
  totalPages: 1,
  total: 0,
};
