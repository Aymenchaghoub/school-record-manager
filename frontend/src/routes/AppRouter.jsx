import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { PrivateRoute } from '../components/common/PrivateRoute';
import { AppLayout } from '../layouts/AppLayout';
import { NotFoundPage } from '../pages/NotFoundPage';
import { UnauthorizedPage } from '../pages/UnauthorizedPage';
import { AbsencesPage } from '../pages/absences/AbsencesPage';
import { LoginPage } from '../pages/auth/LoginPage';
import { ClassesPage } from '../pages/classes/ClassesPage';
import { DashboardPage } from '../pages/dashboard/DashboardPage';
import { EventsPage } from '../pages/events/EventsPage';
import { GradesPage } from '../pages/grades/GradesPage';
import ProfilePage from '../pages/profile/ProfilePage';
import { ReportCardsPage } from '../pages/report-cards/ReportCardsPage';
import { SubjectsPage } from '../pages/subjects/SubjectsPage';
import { UsersPage } from '../pages/users/UsersPage';
import { ROLES } from '../utils/constants';

export function AppRouter() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/403" element={<UnauthorizedPage />} />
        <Route path="/unauthorized" element={<Navigate to="/403" replace />} />

        <Route element={<PrivateRoute />}>
          <Route element={<AppLayout />}>
            <Route index element={<Navigate to="/dashboard" replace />} />
            <Route path="/dashboard" element={<DashboardPage />} />
            <Route path="/profile" element={<ProfilePage />} />

            <Route
              path="/users"
              element={
                <PrivateRoute roles={[ROLES.ADMIN]}>
                  <UsersPage />
                </PrivateRoute>
              }
            />

            <Route
              path="/classes"
              element={
                <PrivateRoute roles={[ROLES.ADMIN]}>
                  <ClassesPage />
                </PrivateRoute>
              }
            />

            <Route
              path="/subjects"
              element={
                <PrivateRoute roles={[ROLES.ADMIN]}>
                  <SubjectsPage />
                </PrivateRoute>
              }
            />

            <Route
              path="/grades"
              element={
                <PrivateRoute roles={[ROLES.ADMIN, ROLES.TEACHER, ROLES.STUDENT, ROLES.PARENT]}>
                  <GradesPage />
                </PrivateRoute>
              }
            />

            <Route
              path="/absences"
              element={
                <PrivateRoute roles={[ROLES.ADMIN, ROLES.TEACHER, ROLES.STUDENT, ROLES.PARENT]}>
                  <AbsencesPage />
                </PrivateRoute>
              }
            />

            <Route
              path="/report-cards"
              element={
                <PrivateRoute roles={[ROLES.ADMIN]}>
                  <ReportCardsPage />
                </PrivateRoute>
              }
            />

            <Route
              path="/events"
              element={
                <PrivateRoute roles={[ROLES.ADMIN]}>
                  <EventsPage />
                </PrivateRoute>
              }
            />
          </Route>
        </Route>

        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </BrowserRouter>
  );
}
