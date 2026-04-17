import { useEffect, useMemo, useState } from 'react';
import { Bar, Line } from 'react-chartjs-2';
import { EmptyState } from '../../components/common/EmptyState';
import { StatCard } from '../../components/common/StatCard';
import { Button } from '../../components/ui/Button';
import { Select } from '../../components/ui/Select';
import { Spinner } from '../../components/ui/Spinner';
import FR from '../../i18n/fr';
import apiClient from '../../services/apiClient';
import {
  getAbsencesPerMonth,
  getAveragePerSubject,
  getGradeEvolution,
  getKpis,
  getStudentsPerClass,
} from '../../services/dashboardService';
import { parseListResponse } from '../../utils/response';

const EMPTY_SERIES = { labels: [], data: [] };
const CHART_COLORS = ['#A855F7', '#E040A0', '#22C55E', '#F97316', '#6366F1'];
const CHART_FONT = {
  family: 'Plus Jakarta Sans',
  size: 12,
};

function StudentsIcon() {
  return (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <circle cx="8" cy="8" r="3" stroke="currentColor" strokeWidth="1.8" />
      <circle cx="16" cy="9" r="2.5" stroke="currentColor" strokeWidth="1.8" />
      <path d="M3.5 18c.5-2.4 2.3-4 4.5-4s4 1.6 4.5 4" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
      <path d="M13.5 18c.4-1.8 1.6-3 3.3-3 1.7 0 2.9 1.2 3.2 3" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
    </svg>
  );
}

function TeacherIcon() {
  return (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M3 7.5 12 3l9 4.5-9 4.5L3 7.5Z" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" />
      <path d="M6 10v4.5c0 1.8 2.7 3.5 6 3.5s6-1.7 6-3.5V10" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
      <path d="M21 8v5" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" />
    </svg>
  );
}

function BellIcon() {
  return (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="M12 4a5 5 0 0 0-5 5v2.6l-1.5 2.7a1 1 0 0 0 .87 1.5h11.26a1 1 0 0 0 .87-1.5L17 11.6V9a5 5 0 0 0-5-5z" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
      <path d="M10 18a2 2 0 0 0 4 0" stroke="currentColor" strokeWidth="1.8" strokeLinecap="round" strokeLinejoin="round" />
    </svg>
  );
}

function StarIcon() {
  return (
    <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path d="m12 3 2.8 5.7L21 9.6l-4.5 4.4 1 6.2L12 17.3l-5.5 2.9 1-6.2L3 9.6l6.2-.9L12 3Z" stroke="currentColor" strokeWidth="1.8" strokeLinejoin="round" />
    </svg>
  );
}

function computeDeltaPercent(current, reference) {
  const safeCurrent = Number(current);
  const safeReference = Number(reference);

  if (!Number.isFinite(safeCurrent) || !Number.isFinite(safeReference) || safeReference === 0) {
    return 0;
  }

  return ((safeCurrent - safeReference) / Math.abs(safeReference)) * 100;
}

function computeAbsenceTrendDelta(series) {
  const values = Array.isArray(series?.data) ? series.data.map(Number).filter(Number.isFinite) : [];
  if (values.length === 0) {
    return 0;
  }

  const current = values[values.length - 1] || 0;
  const previous = values.length > 1 ? values[values.length - 2] || 0 : current;

  if (previous === 0 && current === 0) {
    return 0;
  }

  if (previous === 0) {
    return -100;
  }

  // Fewer absences is positive.
  return ((previous - current) / Math.abs(previous)) * 100;
}

const baseChartOptions = {
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
        font: CHART_FONT,
      },
    },
    y: {
      beginAtZero: true,
      grid: {
        color: '#E2E8F0',
      },
      ticks: {
        color: '#64748B',
        font: CHART_FONT,
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

function KpiSkeletonCard() {
  return (
    <div className="kpi-card">
      <div className="mb-3 h-2 w-20 animate-pulse rounded-full" style={{ background: 'var(--color-border)' }} />
      <div className="h-4 w-24 animate-pulse rounded" style={{ background: 'var(--color-border)' }} />
      <div className="mt-3 h-7 w-16 animate-pulse rounded" style={{ background: 'var(--color-border)' }} />
    </div>
  );
}

function ChartLegend({ labels, values }) {
  if (!Array.isArray(labels) || labels.length === 0) {
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

function ChartCard({ title, loading, error, series, emptyTitle, emptyDescription, children }) {
  return (
    <div className="surface-card p-4">
      <h3 style={{ color: 'var(--color-text)' }}>{title}</h3>

      <div className="relative mt-4 h-[300px]">
        {loading ? (
          <div className="flex h-full items-center justify-center">
            <Spinner label={FR.common.feedback.loading} />
          </div>
        ) : null}

        {!loading && error ? (
          <div className="h-full">
            <EmptyState title={emptyTitle} description={emptyDescription} />
          </div>
        ) : null}

        {!loading && !error && !hasChartData(series) ? (
          <div className="h-full">
            <EmptyState title={emptyTitle} description={emptyDescription} />
          </div>
        ) : null}

        {!loading && !error && hasChartData(series) ? children : null}
      </div>

      {!loading && !error && hasChartData(series) ? (
        <ChartLegend labels={series.labels} values={series.data} />
      ) : null}
    </div>
  );
}

export function AdminDashboard({ payload }) {
  const stats = payload?.stats || payload || {};

  const [kpis, setKpis] = useState({
    total_students: 0,
    total_teachers: 0,
    average_grade: null,
    absences_this_month: 0,
  });
  const [isKpisLoading, setIsKpisLoading] = useState(true);
  const [kpisError, setKpisError] = useState(false);

  const [studentsSeries, setStudentsSeries] = useState(EMPTY_SERIES);
  const [isStudentsLoading, setIsStudentsLoading] = useState(true);
  const [studentsError, setStudentsError] = useState(false);

  const [subjectSeries, setSubjectSeries] = useState(EMPTY_SERIES);
  const [isSubjectsLoading, setIsSubjectsLoading] = useState(true);
  const [subjectsError, setSubjectsError] = useState(false);

  const [absenceSeries, setAbsenceSeries] = useState(EMPTY_SERIES);
  const [isAbsencesLoading, setIsAbsencesLoading] = useState(true);
  const [absencesError, setAbsencesError] = useState(false);

  const [studentOptions, setStudentOptions] = useState([{ value: '', label: 'Selectionner un eleve' }]);
  const [selectedStudentId, setSelectedStudentId] = useState('');

  const [evolutionSeries, setEvolutionSeries] = useState(EMPTY_SERIES);
  const [isEvolutionLoading, setIsEvolutionLoading] = useState(false);
  const [evolutionError, setEvolutionError] = useState(false);
  const [hasEvolutionRequest, setHasEvolutionRequest] = useState(false);

  const totalClasses = Number(stats.total_classes || 0);
  const studentsPerTeacher = kpis.total_teachers > 0 ? kpis.total_students / kpis.total_teachers : 0;
  const studentDelta = computeDeltaPercent(studentsPerTeacher, 25);
  const teacherDelta = computeDeltaPercent(kpis.total_teachers, Math.max(totalClasses, 1));
  const averageDelta = kpis.average_grade === null ? 0 : computeDeltaPercent(kpis.average_grade, 10);
  const absenceDelta = computeAbsenceTrendDelta(absenceSeries);

  useEffect(() => {
    let isMounted = true;

    const loadKpis = async () => {
      setIsKpisLoading(true);
      setKpisError(false);

      try {
        const payload = await getKpis();
        if (isMounted) {
          setKpis({
            total_students: Number(payload?.total_students || 0),
            total_teachers: Number(payload?.total_teachers || 0),
            average_grade: Number.isFinite(Number(payload?.average_grade))
              ? Number(payload.average_grade)
              : null,
            absences_this_month: Number(payload?.absences_this_month || 0),
          });
        }
      } catch {
        if (isMounted) {
          setKpisError(true);
        }
      } finally {
        if (isMounted) {
          setIsKpisLoading(false);
        }
      }
    };

    loadKpis();

    return () => {
      isMounted = false;
    };
  }, []);

  useEffect(() => {
    let isMounted = true;

    const loadStudentsPerClass = async () => {
      setIsStudentsLoading(true);
      setStudentsError(false);

      try {
        const payload = await getStudentsPerClass();
        if (isMounted) {
          setStudentsSeries(normalizeSeries(payload));
        }
      } catch {
        if (isMounted) {
          setStudentsError(true);
        }
      } finally {
        if (isMounted) {
          setIsStudentsLoading(false);
        }
      }
    };

    loadStudentsPerClass();

    return () => {
      isMounted = false;
    };
  }, []);

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

  useEffect(() => {
    let isMounted = true;

    const loadStudents = async () => {
      const candidates = [
        {
          url: '/api/students',
          params: { per_page: 500 },
        },
        {
          url: '/api/v1/admin/users',
          params: { role: 'student', per_page: 500 },
        },
      ];

      for (const candidate of candidates) {
        try {
          const response = await apiClient.get(candidate.url, { params: candidate.params });
          const students = parseListResponse(response.data?.data || response.data).items;

          if (!isMounted) {
            return;
          }

          if (students.length > 0) {
            setStudentOptions([
              { value: '', label: 'Selectionner un eleve' },
              ...students.map((student) => ({ value: String(student.id), label: student.name })),
            ]);
            return;
          }
        } catch {
          // Pass to next candidate endpoint.
        }
      }

      if (isMounted) {
        setStudentOptions([{ value: '', label: 'Selectionner un eleve' }]);
      }
    };

    loadStudents();

    return () => {
      isMounted = false;
    };
  }, []);

  const studentBarChartData = useMemo(
    () => ({
      labels: studentsSeries.labels,
      datasets: [
        {
          label: 'Eleves',
          data: studentsSeries.data,
          backgroundColor: studentsSeries.labels.map((_, index) => CHART_COLORS[index % CHART_COLORS.length]),
          borderRadius: 8,
        },
      ],
    }),
    [studentsSeries]
  );

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

  const subjectChartOptions = {
    ...baseChartOptions,
    scales: {
      ...baseChartOptions.scales,
      y: {
        ...baseChartOptions.scales.y,
        max: 20,
      },
    },
  };

  const loadEvolution = async (event) => {
    event.preventDefault();

    if (!selectedStudentId) {
      setHasEvolutionRequest(false);
      setEvolutionSeries(EMPTY_SERIES);
      return;
    }

    setHasEvolutionRequest(true);
    setIsEvolutionLoading(true);
    setEvolutionError(false);

    try {
      const payload = await getGradeEvolution(Number(selectedStudentId));
      setEvolutionSeries(normalizeSeries(payload));
    } catch {
      setEvolutionError(true);
      setEvolutionSeries(EMPTY_SERIES);
    } finally {
      setIsEvolutionLoading(false);
    }
  };

  return (
    <div className="space-y-5">
      {isKpisLoading ? (
        <div className="grid gap-4 grid-cols-2 lg:grid-cols-4">
          <KpiSkeletonCard />
          <KpiSkeletonCard />
          <KpiSkeletonCard />
          <KpiSkeletonCard />
        </div>
      ) : (
        <div className="grid gap-4 grid-cols-2 lg:grid-cols-4">
          <StatCard
            label={FR.dashboards.admin.kpis.students}
            value={kpisError ? 0 : kpis.total_students}
            delta={kpisError ? 0 : studentDelta}
            icon={<StudentsIcon />}
            accent="cyan"
          />
          <StatCard
            label={FR.dashboards.admin.kpis.teachers}
            value={kpisError ? 0 : kpis.total_teachers}
            delta={kpisError ? 0 : teacherDelta}
            icon={<TeacherIcon />}
            accent="emerald"
          />
          <StatCard
            label={FR.dashboards.admin.kpis.average}
            value={kpisError || kpis.average_grade === null ? '-' : `${kpis.average_grade.toFixed(1)}/20`}
            delta={kpisError ? 0 : averageDelta}
            icon={<StarIcon />}
            accent="amber"
          />
          <StatCard
            label={FR.dashboards.admin.kpis.absences}
            value={kpisError ? 0 : kpis.absences_this_month}
            delta={kpisError ? 0 : absenceDelta}
            icon={<BellIcon />}
            accent="rose"
          />
        </div>
      )}

      <div className="grid gap-4 lg:grid-cols-2">
        <ChartCard
          title={FR.dashboards.admin.charts.studentsPerClass}
          loading={isStudentsLoading}
          error={studentsError}
          series={studentsSeries}
          emptyTitle={FR.dashboards.admin.emptyState.noDataTitle}
          emptyDescription={FR.dashboards.admin.emptyState.studentsPerClassDescription}
        >
          <Bar data={studentBarChartData} options={baseChartOptions} />
        </ChartCard>

        <ChartCard
          title={FR.dashboards.admin.charts.averagePerSubject}
          loading={isSubjectsLoading}
          error={subjectsError}
          series={subjectSeries}
          emptyTitle={FR.dashboards.admin.emptyState.noDataTitle}
          emptyDescription={FR.dashboards.admin.emptyState.averagePerSubjectDescription}
        >
          <Bar data={subjectBarChartData} options={subjectChartOptions} />
        </ChartCard>
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        <ChartCard
          title={FR.dashboards.admin.charts.absencesPerMonth}
          loading={isAbsencesLoading}
          error={absencesError}
          series={absenceSeries}
          emptyTitle={FR.dashboards.admin.emptyState.noDataTitle}
          emptyDescription={FR.dashboards.admin.emptyState.absencesPerMonthDescription}
        >
          <Line data={absencesLineChartData} options={baseChartOptions} />
        </ChartCard>

        <div className="surface-card p-4">
          <h3 style={{ color: 'var(--color-text)' }}>{FR.dashboards.admin.charts.gradesEvolution}</h3>

          <form className="mt-4 grid gap-3 md:grid-cols-[1fr_auto]" onSubmit={loadEvolution}>
            <Select
              label={FR.dashboards.admin.forms.student}
              value={selectedStudentId}
              onChange={(event) => setSelectedStudentId(event.target.value)}
              options={studentOptions}
            />
            <div className="flex items-end">
              <Button type="submit" isLoading={isEvolutionLoading} variant="primary">
                {FR.common.actions.load}
              </Button>
            </div>
          </form>

          <div className="relative mt-4 h-[300px]">
            {isEvolutionLoading ? (
              <div className="flex h-full items-center justify-center">
                <Spinner label={FR.common.feedback.loading} />
              </div>
            ) : null}

            {!isEvolutionLoading && evolutionError ? (
              <div className="h-full">
                <EmptyState
                  title={FR.dashboards.admin.emptyState.noDataTitle}
                  description={FR.dashboards.admin.emptyState.evolutionUnavailable}
                />
              </div>
            ) : null}

            {!isEvolutionLoading && !evolutionError && !hasEvolutionRequest ? (
              <div className="h-full">
                <EmptyState
                  title={FR.dashboards.admin.emptyState.selectStudent}
                  description={FR.dashboards.admin.emptyState.selectStudentDescription}
                />
              </div>
            ) : null}

            {!isEvolutionLoading && !evolutionError && hasEvolutionRequest && !hasChartData(evolutionSeries) ? (
              <div className="h-full">
                <EmptyState
                  title={FR.dashboards.admin.emptyState.noDataTitle}
                  description={FR.dashboards.admin.emptyState.noEvolutionPoint}
                />
              </div>
            ) : null}

            {!isEvolutionLoading && !evolutionError && hasChartData(evolutionSeries) ? (
              <Line data={evolutionLineChartData} options={baseChartOptions} />
            ) : null}
          </div>

          {!isEvolutionLoading && !evolutionError && hasChartData(evolutionSeries) ? (
            <ChartLegend labels={evolutionSeries.labels} values={evolutionSeries.data} />
          ) : null}
        </div>
      </div>
    </div>
  );
}
