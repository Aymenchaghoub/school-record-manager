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

export function StudentDashboard({ payload }) {
  const { user } = useAuth();
  const stats = payload?.stats || payload || {};

  const [evolutionSeries, setEvolutionSeries] = useState(EMPTY_SERIES);
  const [isEvolutionLoading, setIsEvolutionLoading] = useState(true);
  const [evolutionError, setEvolutionError] = useState(false);

  const studentId = useMemo(() => {
    const fallbackId = Number.parseInt(String(user?.id || ''), 10);
    const explicitStudentId = Number.parseInt(String(user?.student_id || ''), 10);

    if (!Number.isNaN(explicitStudentId) && explicitStudentId > 0) {
      return explicitStudentId;
    }

    return Number.isNaN(fallbackId) || fallbackId <= 0 ? null : fallbackId;
  }, [user?.id, user?.student_id]);

  useEffect(() => {
    let isMounted = true;

    const loadEvolution = async () => {
      if (!studentId) {
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
        const payloadData = await getGradeEvolution(studentId);

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
  }, [studentId]);

  const evolutionLineChartData = useMemo(
    () => ({
      labels: evolutionSeries.labels,
      datasets: [
        {
          label: 'Note',
          data: evolutionSeries.data,
          borderColor: 'rgba(14, 116, 144, 1)',
          backgroundColor: 'rgba(14, 116, 144, 0.2)',
          tension: 0.35,
          pointBackgroundColor: 'rgba(14, 116, 144, 1)',
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

  const averageGrade = Number.isFinite(Number(stats.overall_average ?? stats.gpa))
    ? Number(stats.overall_average ?? stats.gpa).toFixed(1)
    : '—';
  const totalAbsences = Number(stats.total_absences ?? 0);

  return (
    <div className="space-y-4">
      <div className="grid gap-4 grid-cols-1 sm:grid-cols-2">
        <StatCard label="Ma moyenne generale" value={averageGrade} accent="cyan" />
        <StatCard label="Mes absences" value={totalAbsences} accent="rose" />
      </div>

      <div className="surface-card p-4">
        <h3 className="text-base font-semibold" style={{ color: 'var(--fg)' }}>
          Evolution des notes
        </h3>

        <div className="relative mt-4 h-[300px]">
          {isEvolutionLoading ? (
            <div className="flex h-full items-center justify-center">
              <Spinner label="Chargement du graphique..." />
            </div>
          ) : null}

          {!isEvolutionLoading && evolutionError ? (
            <div className="h-full">
              <EmptyState
                title="Impossible de charger l'evolution des notes"
                description="L'evolution de vos notes est indisponible pour le moment."
              />
            </div>
          ) : null}

          {!isEvolutionLoading && !evolutionError && !hasChartData(evolutionSeries) ? (
            <div className="h-full">
              <EmptyState
                title="Aucune note disponible"
                description="Le graphique s'affichera des que des notes seront enregistrees."
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
