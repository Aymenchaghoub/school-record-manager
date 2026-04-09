import { useEffect, useState } from 'react';
import { Alert } from '../../components/ui/Alert';
import { Spinner } from '../../components/ui/Spinner';
import { PageHeader } from '../../components/common/PageHeader';
import { useAuth } from '../../hooks/useAuth';
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
            err?.response?.data?.message ||
              'Impossible de charger les donnees dashboard. Configurez les endpoints API Laravel.'
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

  return (
    <div className="space-y-5">
      <PageHeader
        title="Dashboard"
        description="Vue d'ensemble adaptee a votre role"
      />

      {isLoading ? <Spinner label="Chargement du dashboard..." /> : null}
      {error ? <Alert variant="warning">{error}</Alert> : null}
      {!isLoading ? renderRoleDashboard(user?.role, payload) : null}
    </div>
  );
}
