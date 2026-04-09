import { useEffect, useMemo, useState } from 'react';
import { Bar, Line } from 'react-chartjs-2';
import { EmptyState } from '../../components/common/EmptyState';
import { Spinner } from '../../components/ui/Spinner';
import { getAbsencesPerMonth, getAveragePerSubject } from '../../services/dashboardService';

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
          label: 'Average grade',
          data: subjectSeries.data,
          backgroundColor: 'rgba(16, 185, 129, 0.6)',
          borderColor: 'rgba(5, 150, 105, 1)',
          borderWidth: 1,
          borderRadius: 6,
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
          borderColor: 'rgba(244, 63, 94, 1)',
          backgroundColor: 'rgba(244, 63, 94, 0.2)',
          tension: 0.35,
          pointBackgroundColor: 'rgba(244, 63, 94, 1)',
          pointRadius: 4,
        },
      ],
    }),
    [absenceSeries]
  );

  const sharedChartOptions = {
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

  const subjectChartOptions = {
    ...sharedChartOptions,
    scales: {
      y: {
        beginAtZero: true,
        max: 20,
      },
    },
  };

  return (
    <div className="space-y-4">
      <div className="surface-card p-4">
        <h3 className="text-base font-semibold" style={{ color: 'var(--fg)' }}>
          Average Grade by Subject
        </h3>

        <div className="relative mt-4 h-[300px]">
          {isSubjectsLoading ? (
            <div className="flex h-full items-center justify-center">
              <Spinner label="Loading chart..." />
            </div>
          ) : null}

          {!isSubjectsLoading && subjectsError ? (
            <div className="h-full">
              <EmptyState
                title="No subject averages available"
                description="Average grades by subject will appear once subjects have recorded grades."
              />
            </div>
          ) : null}

          {!isSubjectsLoading && !subjectsError && !hasChartData(subjectSeries) ? (
            <div className="h-full">
              <EmptyState
                title="No subject averages available"
                description="Average grades by subject will appear once subjects have recorded grades."
              />
            </div>
          ) : null}

          {!isSubjectsLoading && !subjectsError && hasChartData(subjectSeries) ? (
            <Bar data={subjectBarChartData} options={subjectChartOptions} />
          ) : null}
        </div>
      </div>

      <div className="surface-card p-4">
        <h3 className="text-base font-semibold" style={{ color: 'var(--fg)' }}>
          Absences per Month
        </h3>

        <div className="relative mt-4 h-[300px]">
          {isAbsencesLoading ? (
            <div className="flex h-full items-center justify-center">
              <Spinner label="Loading chart..." />
            </div>
          ) : null}

          {!isAbsencesLoading && absencesError ? (
            <div className="h-full">
              <EmptyState
                title="No absence history available"
                description="Monthly absence trends will appear when absence records are available."
              />
            </div>
          ) : null}

          {!isAbsencesLoading && !absencesError && !hasChartData(absenceSeries) ? (
            <div className="h-full">
              <EmptyState
                title="No absence history available"
                description="Monthly absence trends will appear when absence records are available."
              />
            </div>
          ) : null}

          {!isAbsencesLoading && !absencesError && hasChartData(absenceSeries) ? (
            <Line data={absencesLineChartData} options={sharedChartOptions} />
          ) : null}
        </div>
      </div>
    </div>
  );
}
