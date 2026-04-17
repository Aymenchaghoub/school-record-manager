import { useEffect, useMemo, useState } from 'react';
import { Bar, Line } from 'react-chartjs-2';
import { EmptyState } from '../../components/common/EmptyState';
import { Spinner } from '../../components/ui/Spinner';
import { getAbsencesPerMonth, getAveragePerSubject } from '../../services/dashboardService';

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

export function TeacherDashboard() {
  const [subjectSeries, setSubjectSeries] = useState(EMPTY_SERIES);
  const [isSubjectsLoading, setIsSubjectsLoading] = useState(true);
  const [subjectsError, setSubjectsError] = useState(false);

  const [absenceSeries, setAbsenceSeries] = useState(EMPTY_SERIES);
  const [isAbsencesLoading, setIsAbsencesLoading] = useState(true);
  const [absencesError, setAbsencesError] = useState(false);

  useEffect(() => {
    let isMounted = true;

    const loadAveragePerSubject = async () => {
      setIsSubjectsLoading(true);
      setSubjectsError(false);

      try {
        const payload = await getAveragePerSubject();
        if (isMounted) {
          setSubjectSeries(normalizeSeries(payload));
        }
      } catch {
        if (isMounted) {
          setSubjectsError(true);
        }
      } finally {
        if (isMounted) {
          setIsSubjectsLoading(false);
        }
      }
    };

    loadAveragePerSubject();

    return () => {
      isMounted = false;
    };
  }, []);

  useEffect(() => {
    let isMounted = true;

    const loadAbsencesPerMonth = async () => {
      setIsAbsencesLoading(true);
      setAbsencesError(false);

      try {
        const payload = await getAbsencesPerMonth();
        if (isMounted) {
          setAbsenceSeries(normalizeSeries(payload));
        }
      } catch {
        if (isMounted) {
          setAbsencesError(true);
        }
      } finally {
        if (isMounted) {
          setIsAbsencesLoading(false);
        }
      }
    };

    loadAbsencesPerMonth();

    return () => {
      isMounted = false;
    };
  }, []);

  const subjectBarChartData = useMemo(
    () => ({
      labels: subjectSeries.labels,
      datasets: [
        {
          label: 'Moyenne',
          data: subjectSeries.data,
          backgroundColor: subjectSeries.labels.map((_, index) => CHART_COLORS[index % CHART_COLORS.length]),
          borderRadius: 8,
        },
      ],
    }),
    [subjectSeries]
  );

  const absencesLineChartData = useMemo(
    () => ({
      labels: absenceSeries.labels,
      datasets: [
        {
          label: 'Absences',
          data: absenceSeries.data,
          borderColor: CHART_COLORS[1],
          backgroundColor: 'rgba(224, 64, 160, 0.2)',
          tension: 0.35,
          pointBackgroundColor: CHART_COLORS[1],
          pointRadius: 4,
        },
      ],
    }),
    [absenceSeries]
  );

  const subjectChartOptions = {
    ...chartOptions,
    scales: {
      ...chartOptions.scales,
      y: {
        ...chartOptions.scales.y,
        max: 20,
      },
    },
  };

  return (
    <div className="space-y-4">
      <div className="surface-card p-4">
        <h3 style={{ color: 'var(--color-text)' }}>Moyenne par matiere</h3>

        <div className="relative mt-4 h-[300px]">
          {isSubjectsLoading ? (
            <div className="flex h-full items-center justify-center">
              <Spinner label="Chargement..." />
            </div>
          ) : null}

          {!isSubjectsLoading && subjectsError ? (
            <div className="h-full">
              <EmptyState
                title="Aucune donnee"
                description="Les moyennes par matiere apparaitront des que des notes seront enregistrees."
              />
            </div>
          ) : null}

          {!isSubjectsLoading && !subjectsError && !hasChartData(subjectSeries) ? (
            <div className="h-full">
              <EmptyState
                title="Aucune donnee"
                description="Les moyennes par matiere apparaitront des que des notes seront enregistrees."
              />
            </div>
          ) : null}

          {!isSubjectsLoading && !subjectsError && hasChartData(subjectSeries) ? (
            <Bar data={subjectBarChartData} options={subjectChartOptions} />
          ) : null}
        </div>

        {!isSubjectsLoading && !subjectsError && hasChartData(subjectSeries) ? (
          <ChartLegend labels={subjectSeries.labels} values={subjectSeries.data} />
        ) : null}
      </div>

      <div className="surface-card p-4">
        <h3 style={{ color: 'var(--color-text)' }}>Absences par mois</h3>

        <div className="relative mt-4 h-[300px]">
          {isAbsencesLoading ? (
            <div className="flex h-full items-center justify-center">
              <Spinner label="Chargement..." />
            </div>
          ) : null}

          {!isAbsencesLoading && absencesError ? (
            <div className="h-full">
              <EmptyState
                title="Aucune donnee"
                description="La tendance des absences apparaitra lorsque des absences seront enregistrees."
              />
            </div>
          ) : null}

          {!isAbsencesLoading && !absencesError && !hasChartData(absenceSeries) ? (
            <div className="h-full">
              <EmptyState
                title="Aucune donnee"
                description="La tendance des absences apparaitra lorsque des absences seront enregistrees."
              />
            </div>
          ) : null}

          {!isAbsencesLoading && !absencesError && hasChartData(absenceSeries) ? (
            <Line data={absencesLineChartData} options={chartOptions} />
          ) : null}
        </div>

        {!isAbsencesLoading && !absencesError && hasChartData(absenceSeries) ? (
          <ChartLegend labels={absenceSeries.labels} values={absenceSeries.data} />
        ) : null}
      </div>
    </div>
  );
}
