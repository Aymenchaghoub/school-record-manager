import { useEffect, useMemo, useState } from 'react';
import { Bar, Line } from 'react-chartjs-2';
import { EmptyState } from '../../components/common/EmptyState';
import { StatCard } from '../../components/common/StatCard';
import { Button } from '../../components/ui/Button';
import { Select } from '../../components/ui/Select';
import { Spinner } from '../../components/ui/Spinner';
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
            <Spinner label="Chargement..." />
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

export function AdminDashboard() {
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
          params: { per_page: 100 },
        },
        {
          url: '/api/v1/admin/users',
          params: { role: 'student', per_page: 100 },
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
          <StatCard label="Total eleves" value={kpisError ? 0 : kpis.total_students} accent="cyan" />
          <StatCard label="Total enseignants" value={kpisError ? 0 : kpis.total_teachers} accent="emerald" />
          <StatCard
            label="Moyenne generale"
            value={kpisError || kpis.average_grade === null ? '-' : `${kpis.average_grade.toFixed(1)}/20`}
            accent="amber"
          />
          <StatCard label="Absences du mois" value={kpisError ? 0 : kpis.absences_this_month} accent="rose" />
        </div>
      )}

      <div className="grid gap-4 lg:grid-cols-2">
        <ChartCard
          title="Eleves par classe"
          loading={isStudentsLoading}
          error={studentsError}
          series={studentsSeries}
          emptyTitle="Aucune donnee"
          emptyDescription="La repartition des eleves apparaitra des que les classes auront des inscriptions."
        >
          <Bar data={studentBarChartData} options={baseChartOptions} />
        </ChartCard>

        <ChartCard
          title="Moyenne par matiere"
          loading={isSubjectsLoading}
          error={subjectsError}
          series={subjectSeries}
          emptyTitle="Aucune donnee"
          emptyDescription="Les moyennes par matiere apparaitront des que des notes seront enregistrees."
        >
          <Bar data={subjectBarChartData} options={subjectChartOptions} />
        </ChartCard>
      </div>

      <div className="grid gap-4 lg:grid-cols-2">
        <ChartCard
          title="Absences par mois"
          loading={isAbsencesLoading}
          error={absencesError}
          series={absenceSeries}
          emptyTitle="Aucune donnee"
          emptyDescription="La tendance des absences apparaitra lorsque des absences seront enregistrees."
        >
          <Line data={absencesLineChartData} options={baseChartOptions} />
        </ChartCard>

        <div className="surface-card p-4">
          <h3 style={{ color: 'var(--color-text)' }}>Evolution des notes</h3>

          <form className="mt-4 grid gap-3 md:grid-cols-[1fr_auto]" onSubmit={loadEvolution}>
            <Select
              label="Eleve"
              value={selectedStudentId}
              onChange={(event) => setSelectedStudentId(event.target.value)}
              options={studentOptions}
            />
            <div className="flex items-end">
              <Button type="submit" isLoading={isEvolutionLoading}>
                Charger
              </Button>
            </div>
          </form>

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
                  description="Impossible de charger l'evolution des notes pour l'eleve selectionne."
                />
              </div>
            ) : null}

            {!isEvolutionLoading && !evolutionError && !hasEvolutionRequest ? (
              <div className="h-full">
                <EmptyState
                  title="Selectionnez un eleve"
                  description="Choisissez un eleve puis cliquez sur Charger pour afficher son evolution."
                />
              </div>
            ) : null}

            {!isEvolutionLoading && !evolutionError && hasEvolutionRequest && !hasChartData(evolutionSeries) ? (
              <div className="h-full">
                <EmptyState
                  title="Aucune donnee"
                  description="Aucun point d'evolution disponible pour l'eleve selectionne."
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
