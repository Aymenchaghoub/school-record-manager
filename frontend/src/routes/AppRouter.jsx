import { BrowserRouter, Navigate, Route, Routes } from 'react-router-dom';
import { ProtectedRoute } from '../components/common/ProtectedRoute';
import { AppLayout } from '../layouts/AppLayout';
import { NotFoundPage } from '../pages/NotFoundPage';
import { UnauthorizedPage } from '../pages/UnauthorizedPage';
import { AbsencesPage } from '../pages/absences/AbsencesPage';
import { LoginPage } from '../pages/auth/LoginPage';
import { ClassesPage } from '../pages/classes/ClassesPage';
import { DashboardPage } from '../pages/dashboard/DashboardPage';
import { EventsPage } from '../pages/events/EventsPage';
import { GradesPage } from '../pages/grades/GradesPage';
import { ReportCardsPage } from '../pages/report-cards/ReportCardsPage';
import { SubjectsPage } from '../pages/subjects/SubjectsPage';
import { UsersPage } from '../pages/users/UsersPage';
import { ROLES } from '../utils/constants';

export function AppRouter() {
  return (
    <BrowserRouter>
      <Routes>
        <Route path="/login" element={<LoginPage />} />
        <Route path="/unauthorized" element={<UnauthorizedPage />} />

        <Route element={<ProtectedRoute />}>
          <Route element={<AppLayout />}>
            <Route index element={<Navigate to="/dashboard" replace />} />
            <Route path="/dashboard" element={<DashboardPage />} />

            <Route
              path="/users"
              element={
                <ProtectedRoute roles={[ROLES.ADMIN]}>
                  <UsersPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/classes"
              element={
                <ProtectedRoute roles={[ROLES.ADMIN, ROLES.TEACHER]}>
                  <ClassesPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/subjects"
              element={
                <ProtectedRoute roles={[ROLES.ADMIN, ROLES.TEACHER]}>
                  <SubjectsPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/grades"
              element={
                <ProtectedRoute roles={[ROLES.ADMIN, ROLES.TEACHER, ROLES.STUDENT, ROLES.PARENT]}>
                  <GradesPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/absences"
              element={
                <ProtectedRoute roles={[ROLES.ADMIN, ROLES.TEACHER, ROLES.STUDENT, ROLES.PARENT]}>
                  <AbsencesPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/report-cards"
              element={
                <ProtectedRoute roles={[ROLES.ADMIN, ROLES.STUDENT, ROLES.PARENT]}>
                  <ReportCardsPage />
                </ProtectedRoute>
              }
            />

            <Route
              path="/events"
              element={
                <ProtectedRoute roles={[ROLES.ADMIN, ROLES.TEACHER, ROLES.STUDENT, ROLES.PARENT]}>
                  <EventsPage />
                </ProtectedRoute>
              }
            />
          </Route>
        </Route>

        <Route path="*" element={<NotFoundPage />} />
      </Routes>
    </BrowserRouter>
  );
}
