export const ADMIN_EMAIL = process.env.E2E_ADMIN_EMAIL || 'admin@school.com';
export const ADMIN_PASSWORD = process.env.E2E_ADMIN_PASSWORD || 'password';

const ADMIN_USER = {
  id: 1,
  name: 'Admin User',
  email: ADMIN_EMAIL,
  role: 'admin',
};

const STUDENT_USER = {
  id: 101,
  name: 'Student User',
  email: 'student@school.com',
  role: 'student',
};

const TEACHER_USER = {
  id: 102,
  name: 'Teacher User',
  email: 'teacher@school.com',
  role: 'teacher',
};

const CLASS_ITEM = {
  id: 301,
  name: 'Grade 1 - Section A',
};

const SUBJECT_ITEM = {
  id: 201,
  name: 'Mathematics',
};

function fulfillJson(route, status, payload) {
  return route.fulfill({
    status,
    contentType: 'application/json',
    body: JSON.stringify(payload),
  });
}

export async function mockSchoolApi(page, options = {}) {
  const { allowLogin = true } = options;

  let isAuthenticated = false;

  const requireAuth = (route, payload) => {
    if (!isAuthenticated) {
      return fulfillJson(route, 401, { message: 'Unauthenticated.' });
    }

    return fulfillJson(route, 200, payload);
  };

  await page.route('**/sanctum/csrf-cookie', (route) => route.fulfill({ status: 204, body: '' }));

  const loginHandler = async (route, request) => {
    const payload = request.postDataJSON?.() || {};
    const isValidCredentials = payload.email === ADMIN_EMAIL && payload.password === ADMIN_PASSWORD;

    if (!allowLogin || !isValidCredentials) {
      isAuthenticated = false;
      return fulfillJson(route, 422, { message: 'Identifiants invalides.' });
    }

    isAuthenticated = true;
    return fulfillJson(route, 200, { message: 'OK' });
  };

  await page.route('**/api/v1/login', loginHandler);
  await page.route('**/api/login', loginHandler);

  const currentUserHandler = (route) => {
    if (!isAuthenticated) {
      return fulfillJson(route, 401, { message: 'Unauthenticated.' });
    }

    return fulfillJson(route, 200, { data: { user: ADMIN_USER } });
  };

  await page.route('**/api/v1/user', currentUserHandler);
  await page.route('**/api/user', currentUserHandler);

  await page.route('**/api/v1/logout', (route) => {
    isAuthenticated = false;
    return fulfillJson(route, 200, { message: 'Logged out' });
  });
  await page.route('**/api/logout', (route) => {
    isAuthenticated = false;
    return fulfillJson(route, 200, { message: 'Logged out' });
  });

  await page.route('**/api/v1/admin/dashboard', (route) => requireAuth(route, {
    stats: {
      total_classes: 1,
      total_students: 1,
      total_teachers: 1,
      average_grade: 15.2,
      absences_this_month: 1,
    },
  }));

  await page.route('**/api/v1/dashboard/kpis', (route) => requireAuth(route, {
    total_students: 1,
    total_teachers: 1,
    average_grade: 15.2,
    absences_this_month: 1,
  }));

  await page.route('**/api/v1/dashboard/students-per-class', (route) => requireAuth(route, {
    labels: [CLASS_ITEM.name],
    data: [1],
  }));

  await page.route('**/api/v1/dashboard/average-per-subject', (route) => requireAuth(route, {
    labels: [SUBJECT_ITEM.name],
    data: [15.2],
  }));

  await page.route('**/api/v1/dashboard/absences-per-month', (route) => requireAuth(route, {
    labels: ['Jan'],
    data: [1],
  }));

  await page.route('**/api/v1/dashboard/grade-evolution**', (route) => requireAuth(route, {
    labels: ['Quiz 1'],
    data: [15.2],
  }));

  await page.route('**/api/v1/admin/users**', (route, request) => {
    if (!isAuthenticated) {
      return fulfillJson(route, 401, { message: 'Unauthenticated.' });
    }

    const url = new URL(request.url());
    const role = url.searchParams.get('role');

    if (role === 'student') {
      return fulfillJson(route, 200, { data: [STUDENT_USER] });
    }

    if (role === 'teacher') {
      return fulfillJson(route, 200, { data: [TEACHER_USER] });
    }

    return fulfillJson(route, 200, { data: [ADMIN_USER] });
  });

  await page.route('**/api/v1/admin/classes**', (route) => requireAuth(route, { data: [CLASS_ITEM] }));
  await page.route('**/api/v1/admin/subjects**', (route) => requireAuth(route, { data: [SUBJECT_ITEM] }));

  await page.route('**/api/v1/admin/grades**', (route) => requireAuth(route, {
    data: [
      {
        id: 1,
        student_id: STUDENT_USER.id,
        subject_id: SUBJECT_ITEM.id,
        class_id: CLASS_ITEM.id,
        value: 16,
        type: 'exam',
        term: 'Term 1',
        grade_date: '2025-01-10',
        student: STUDENT_USER,
        subject: SUBJECT_ITEM,
        class: CLASS_ITEM,
      },
    ],
  }));

  await page.route('**/api/v1/admin/report-cards**', (route) => requireAuth(route, {
    data: [
      {
        id: 1,
        student_id: STUDENT_USER.id,
        class_id: CLASS_ITEM.id,
        term: 'Term 1',
        academic_year: '2024-2025',
        overall_average: 15.2,
        total_absences: 1,
        justified_absences: 1,
        is_final: true,
        issue_date: '2025-01-31',
        subjects: [
          { subject: SUBJECT_ITEM.name, average: 15.2 },
        ],
        student: STUDENT_USER,
        class: CLASS_ITEM,
      },
    ],
  }));

  await page.route('**/broadcasting/auth', (route) => requireAuth(route, { auth: 'mock-auth-token' }));
}
