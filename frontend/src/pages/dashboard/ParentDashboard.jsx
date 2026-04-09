import { useEffect, useMemo, useState } from 'react';
import { Line } from 'react-chartjs-2';
import { EmptyState } from '../../components/common/EmptyState';
import { StatCard } from '../../components/common/StatCard';
import { Spinner } from '../../components/ui/Spinner';
import { useAuth } from '../../hooks/useAuth';
import { getGradeEvolution } from '../../services/dashboardService';

const EMPTY_SERIES = { labels: [], data: [] };

function normalizeSeries(payload) {
  return {
    labels: Array.isArray(payload?.labels) ? payload.labels : [],
    data: Array.isArray(payload?.data) ? payload.data : [],
  };
}

function hasChartData(series) {
  return Array.isArray(series.labels)
    && series.labels.length > 0
    && Array.isArray(series.data)
    && series.data.length > 0;
}

function resolveLinkedChildId(user) {
  const candidates = [
    user?.child_id,
    user?.student_id,
    user?.childId,
    user?.studentId,
    Array.isArray(user?.children) ? user.children[0]?.id : null,
  ];

  for (const candidate of candidates) {
    const parsed = Number.parseInt(String(candidate ?? ''), 10);
    if (!Number.isNaN(parsed) && parsed > 0) {
      return parsed;
    }
  }

  return null;
}

export function ParentDashboard({ payload }) {
  const { user } = useAuth();
  const stats = payload?.stats || payload || {};

  const linkedChildId = useMemo(() => resolveLinkedChildId(user), [user]);

  const [evolutionSeries, setEvolutionSeries] = useState(EMPTY_SERIES);
  const [isEvolutionLoading, setIsEvolutionLoading] = useState(true);
  const [evolutionError, setEvolutionError] = useState(false);

  useEffect(() => {
    let isMounted = true;

    const loadEvolution = async () => {
      if (!linkedChildId) {
        if (isMounted) {
          setEvolutionSeries(EMPTY_SERIES);
          setIsEvolutionLoading(false);
          setEvolutionError(false);
        }
        return;
      }

      setIsEvolutionLoading(true);
      setEvolutionError(false);

      try {
        const payloadData = await getGradeEvolution(linkedChildId);

        if (isMounted) {
          setEvolutionSeries(normalizeSeries(payloadData));
        }
      } catch {
        if (isMounted) {
          setEvolutionError(true);
          setEvolutionSeries(EMPTY_SERIES);
        }
      } finally {
        if (isMounted) {
          setIsEvolutionLoading(false);
        }
      }
    };

    loadEvolution();

    return () => {
      isMounted = false;
    };
  }, [linkedChildId]);

  const evolutionLineChartData = useMemo(
    () => ({
      labels: evolutionSeries.labels,
      datasets: [
        {
          label: 'Grade',
          data: evolutionSeries.data,
          borderColor: 'rgba(139, 92, 246, 1)',
          backgroundColor: 'rgba(139, 92, 246, 0.2)',
          tension: 0.35,
          pointBackgroundColor: 'rgba(139, 92, 246, 1)',
          pointRadius: 4,
        },
      ],
    }),
    [evolutionSeries]
  );

  const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
      legend: {
        display: false,
      },
    },
    scales: {
      y: {
        beginAtZero: true,
      },
    },
  };

  if (!linkedChildId) {
    return (
      <EmptyState
        title="No child linked to your account"
        description="Link a child profile to your account to view grade evolution and attendance metrics."
      />
    );
  }

  const averageGrade = Number.isFinite(Number(stats.average_grade))
    ? Number(stats.average_grade).toFixed(1)
    : '—';
  const totalAbsences = Number(stats.total_absences ?? 0);

  return (
    <div className="space-y-4">
      <div className="grid gap-4 grid-cols-1 sm:grid-cols-2">
        <StatCard label="Child Average Grade" value={averageGrade} accent="cyan" />
        <StatCard label="Child Total Absences" value={totalAbsences} accent="rose" />
      </div>

      <div className="surface-card p-4">
        <h3 className="text-base font-semibold" style={{ color: 'var(--fg)' }}>
          Grade Evolution
        </h3>

        <div className="relative mt-4 h-[300px]">
          {isEvolutionLoading ? (
            <div className="flex h-full items-center justify-center">
              <Spinner label="Loading chart..." />
            </div>
          ) : null}

          {!isEvolutionLoading && evolutionError ? (
            <div className="h-full">
              <EmptyState
                title="Unable to load grade evolution"
                description="Child grade evolution could not be loaded right now."
              />
            </div>
          ) : null}

          {!isEvolutionLoading && !evolutionError && !hasChartData(evolutionSeries) ? (
            <div className="h-full">
              <EmptyState
                title="No grade data available yet"
                description="The evolution chart will appear once your child's grades are recorded."
              />
            </div>
          ) : null}

          {!isEvolutionLoading && !evolutionError && hasChartData(evolutionSeries) ? (
            <Line data={evolutionLineChartData} options={chartOptions} />
          ) : null}
        </div>
      </div>
    </div>
  );
}
