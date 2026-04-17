import { useEffect, useState } from 'react';
import { EmptyState } from '../../components/common/EmptyState';
import { Alert } from '../../components/ui/Alert';
import { Spinner } from '../../components/ui/Spinner';
import { PageHeader } from '../../components/common/PageHeader';
import { useAuth } from '../../hooks/useAuth';
import FR from '../../i18n/fr';
import { dashboardService } from '../../services/dashboardService';
import { ROLES } from '../../utils/constants';
import { AdminDashboard } from './AdminDashboard';
import { TeacherDashboard } from './TeacherDashboard';
import { StudentDashboard } from './StudentDashboard';
import { ParentDashboard } from './ParentDashboard';

function renderRoleDashboard(role, payload) {
  if (role === ROLES.ADMIN) {
    return <AdminDashboard payload={payload} />;
  }

  if (role === ROLES.TEACHER) {
    return <TeacherDashboard payload={payload} />;
  }

  if (role === ROLES.STUDENT) {
    return <StudentDashboard payload={payload} />;
  }

  return <ParentDashboard payload={payload} />;
}

function hasDashboardData(payload) {
  const source = payload?.stats && typeof payload.stats === 'object' ? payload.stats : payload;

  if (!source || typeof source !== 'object') {
    return false;
  }

  return Object.values(source).some((value) => value !== null && value !== undefined && value !== '');
}

export function DashboardPage() {
  const { user } = useAuth();
  const [payload, setPayload] = useState({});
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');

  useEffect(() => {
    let isMounted = true;

    const run = async () => {
      if (!user?.role) {
        setIsLoading(false);
        return;
      }

      setIsLoading(true);
      setError('');

      try {
        const result = await dashboardService.getDashboardByRole(user.role);
        if (isMounted) {
          setPayload(result || {});
        }
      } catch (err) {
        if (isMounted) {
          setError(
            err?.message ||
              FR.common.errors.load
          );
        }
      } finally {
        if (isMounted) {
          setIsLoading(false);
        }
      }
    };

    run();

    return () => {
      isMounted = false;
    };
  }, [user?.role]);

  const showEmptyState = !isLoading && !error && !hasDashboardData(payload);

  return (
    <div className="space-y-5">
      <PageHeader
        title="Tableau de bord"
        description="Vue d'ensemble adaptee a votre role"
      />

      {isLoading ? <Spinner label="Chargement du tableau de bord..." /> : null}
      {error ? <Alert variant="warning">{error}</Alert> : null}
      {showEmptyState ? (
        <EmptyState
          title="Aucune donnee disponible"
          description="Les indicateurs apparaitront des que des activites seront enregistrees."
        />
      ) : null}
      {!isLoading && !showEmptyState ? renderRoleDashboard(user?.role, payload) : null}
    </div>
  );
}
