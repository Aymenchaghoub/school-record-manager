import { useEffect, useMemo, useState } from 'react';
import { Line } from 'react-chartjs-2';
import { EmptyState } from '../../components/common/EmptyState';
import { StatCard } from '../../components/common/StatCard';
import { Spinner } from '../../components/ui/Spinner';
import { useAuth } from '../../hooks/useAuth';
import { getGradeEvolution } from '../../services/dashboardService';

const EMPTY_SERIES = { labels: [], data: [] };
const CHART_COLORS = ['#A855F7', '#E040A0', '#22C55E', '#F97316', '#6366F1'];

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: {
      display: false,
    },
  },
  scales: {
    x: {
      grid: {
        color: '#E2E8F0',
      },
      ticks: {
        color: '#64748B',
        font: {
          family: 'Plus Jakarta Sans',
          size: 12,
        },
      },
    },
    y: {
      beginAtZero: true,
      grid: {
        color: '#E2E8F0',
      },
      ticks: {
        color: '#64748B',
        font: {
          family: 'Plus Jakarta Sans',
          size: 12,
        },
      },
    },
  },
};

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

function ChartLegend({ labels, values }) {
  if (!labels.length) {
    return null;
  }

  return (
    <div className="chart-legend">
      {labels.map((label, index) => (
        <div key={`${label}-${index}`} className="chart-legend-item">
          <span
            className="chart-legend-swatch"
            style={{ backgroundColor: CHART_COLORS[index % CHART_COLORS.length] }}
            aria-hidden="true"
          />
          <span>{label}</span>
          <strong style={{ color: 'var(--color-text)' }}>{values[index] ?? 0}</strong>
        </div>
      ))}
    </div>
  );
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
          borderColor: CHART_COLORS[0],
          backgroundColor: 'rgba(168, 85, 247, 0.2)',
          tension: 0.35,
          pointBackgroundColor: CHART_COLORS[0],
          pointRadius: 4,
        },
      ],
    }),
    [evolutionSeries]
  );

  const averageGrade = Number.isFinite(Number(stats.overall_average ?? stats.gpa))
    ? `${Number(stats.overall_average ?? stats.gpa).toFixed(1)}/20`
    : '-';
  const totalAbsences = Number(stats.total_absences ?? 0);

  return (
    <div className="space-y-4">
      <div className="grid gap-4 grid-cols-1 sm:grid-cols-2">
        <StatCard label="Ma moyenne generale" value={averageGrade} accent="cyan" />
        <StatCard label="Mes absences" value={totalAbsences} accent="rose" />
      </div>

      <div className="surface-card p-4">
        <h3 style={{ color: 'var(--color-text)' }}>Evolution des notes</h3>

        <div className="relative mt-4 h-[300px]">
          {isEvolutionLoading ? (
            <div className="flex h-full items-center justify-center">
              <Spinner label="Chargement..." />
            </div>
          ) : null}

          {!isEvolutionLoading && evolutionError ? (
            <div className="h-full">
              <EmptyState
                title="Aucune donnee"
                description="Impossible de charger l'evolution de vos notes pour le moment."
              />
            </div>
          ) : null}

          {!isEvolutionLoading && !evolutionError && !hasChartData(evolutionSeries) ? (
            <div className="h-full">
              <EmptyState
                title="Aucune donnee"
                description="Le graphique apparaitra des que des notes seront enregistrees."
              />
            </div>
          ) : null}

          {!isEvolutionLoading && !evolutionError && hasChartData(evolutionSeries) ? (
            <Line data={evolutionLineChartData} options={chartOptions} />
          ) : null}
        </div>

        {!isEvolutionLoading && !evolutionError && hasChartData(evolutionSeries) ? (
          <ChartLegend labels={evolutionSeries.labels} values={evolutionSeries.data} />
        ) : null}
      </div>
    </div>
  );
}
