import { useEffect, useMemo, useState } from 'react';
import { Bar, Line } from 'react-chartjs-2';
import { EmptyState } from '../../components/common/EmptyState';
import { StatCard } from '../../components/common/StatCard';
import { Button } from '../../components/ui/Button';
import { Input } from '../../components/ui/Input';
import { Spinner } from '../../components/ui/Spinner';
import {
  getAbsencesPerMonth,
  getAveragePerSubject,
  getGradeEvolution,
  getKpis,
  getStudentsPerClass,
} from '../../services/dashboardService';

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

function KpiSkeletonCard() {
  return (
    <div className="surface-card p-4">
      <div className="mb-3 h-2 w-20 animate-pulse rounded-full bg-slate-300/70" />
      <div className="h-4 w-28 animate-pulse rounded bg-slate-300/70" />
      <div className="mt-3 h-7 w-20 animate-pulse rounded bg-slate-300/70" />
    </div>
  );
}

function ChartCard({ title, loading, error, series, emptyTitle, emptyDescription, children }) {
  return (
    <div className="surface-card p-4">
      <h3 className="text-base font-semibold" style={{ color: 'var(--fg)' }}>
        {title}
      </h3>

      <div className="relative mt-4 h-[300px]">
        {loading ? (
          <div className="flex h-full items-center justify-center">
            <Spinner label="Loading chart..." />
          </div>
        ) : null}

        {!loading && error ? (
          <div className="h-full">
            <EmptyState
              title={emptyTitle}
              description={emptyDescription}
            />
          </div>
        ) : null}

        {!loading && !error && !hasChartData(series) ? (
          <div className="h-full">
            <EmptyState
              title={emptyTitle}
              description={emptyDescription}
            />
          </div>
        ) : null}

        {!loading && !error && hasChartData(series) ? children : null}
      </div>
    </div>
  );
}

export function AdminDashboard() {
  const [kpis, setKpis] = useState({
    total_students: 0,
    total_teachers: 0,
    average_grade: 0,
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

  const [studentIdInput, setStudentIdInput] = useState('');
  const [evolutionSeries, setEvolutionSeries] = useState(EMPTY_SERIES);
  const [isEvolutionLoading, setIsEvolutionLoading] = useState(false);
  const [evolutionError, setEvolutionError] = useState(false);
  const [hasEvolutionRequest, setHasEvolutionRequest] = useState(false);

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
            average_grade: Number(payload?.average_grade || 0),
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

  const studentBarChartData = useMemo(
    () => ({
      labels: studentsSeries.labels,
      datasets: [
        {
          label: 'Students',
          data: studentsSeries.data,
          backgroundColor: 'rgba(59, 130, 246, 0.65)',
          borderColor: 'rgba(37, 99, 235, 1)',
          borderWidth: 1,
          borderRadius: 6,
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

  const effectiveKpis = kpisError
    ? {
      total_students: 0,
      total_teachers: 0,
      average_grade: '—',
      absences_this_month: 0,
    }
    : {
      ...kpis,
      average_grade: Number.isFinite(kpis.average_grade) ? kpis.average_grade.toFixed(1) : '0.0',
    };

  const loadEvolution = async (event) => {
    event.preventDefault();

    setHasEvolutionRequest(true);
    setIsEvolutionLoading(true);
    setEvolutionError(false);

    try {
      const parsedStudentId = Number.parseInt(studentIdInput, 10);
      const payload = await getGradeEvolution(
        Number.isNaN(parsedStudentId) || parsedStudentId <= 0 ? undefined : parsedStudentId
      );
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
          <StatCard label="👨‍🎓 Total Students" value={effectiveKpis.total_students} accent="cyan" />
          <StatCard label="👨‍🏫 Total Teachers" value={effectiveKpis.total_teachers} accent="emerald" />
          <StatCard label="📊 Average Grade" value={effectiveKpis.average_grade} accent="amber" />
          <StatCard label="📅 Absences This Month" value={effectiveKpis.absences_this_month} accent="rose" />
        </div>
      )}

      <div className="grid gap-4 lg:grid-cols-2">
        <ChartCard
          title="Students per Class"
          loading={isStudentsLoading}
          error={studentsError}
          series={studentsSeries}
          emptyTitle="No class data available"
          emptyDescription="Student distribution per class will appear once class enrollment data exists."
        >
          <Bar data={studentBarChartData} options={sharedChartOptions} />
        </ChartCard>

        <ChartCard
          title="Average Grade by Subject"
          loading={isSubjectsLoading}
          error={subjectsError}
          series={subjectSeries}
          emptyTitle="No subject averages available"
          emptyDescription="Average grades by subject will appear once subjects have recorded grades."
        >
          <Bar data={subjectBarChartData} options={subjectChartOptions} />
        </ChartCard>
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        <ChartCard
          title="Absences per Month"
          loading={isAbsencesLoading}
          error={absencesError}
          series={absenceSeries}
          emptyTitle="No absence history available"
          emptyDescription="Monthly absence trends will appear when absence records are available."
        >
          <Line data={absencesLineChartData} options={sharedChartOptions} />
        </ChartCard>

        <div className="surface-card p-4">
          <h3 className="text-base font-semibold" style={{ color: 'var(--fg)' }}>
            Grade Evolution
          </h3>

          <form className="mt-4 flex flex-col gap-3 md:flex-row" onSubmit={loadEvolution}>
            <Input
              type="number"
              min="1"
              label="Student ID"
              value={studentIdInput}
              onChange={(event) => setStudentIdInput(event.target.value)}
              placeholder="Enter student ID"
            />
            <div className="flex items-end">
              <Button type="submit" isLoading={isEvolutionLoading}>
                Load
              </Button>
            </div>
          </form>

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
                  description="Try another student ID or verify that grade data exists for this student."
                />
              </div>
            ) : null}

            {!isEvolutionLoading && !evolutionError && !hasEvolutionRequest ? (
              <div className="h-full">
                <EmptyState
                  title="Load a student to view evolution"
                  description="Enter a student ID and click Load to render the grade evolution chart."
                />
              </div>
            ) : null}

            {!isEvolutionLoading && !evolutionError && hasEvolutionRequest && !hasChartData(evolutionSeries) ? (
              <div className="h-full">
                <EmptyState
                  title="No grade data found"
                  description="No grade evolution points are available for the selected student."
                />
              </div>
            ) : null}

            {!isEvolutionLoading && !evolutionError && hasChartData(evolutionSeries) ? (
              <Line data={evolutionLineChartData} options={sharedChartOptions} />
            ) : null}
          </div>
        </div>
      </div>
    </div>
  );
}
