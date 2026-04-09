import { StatCard } from '../../components/common/StatCard';

export function ParentDashboard({ payload }) {
  const stats = payload?.stats || payload || {};

  return (
    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Enfants" value={stats.total_children ?? 0} accent="cyan" />
      <StatCard label="Moyenne globale" value={stats.average_grade ?? '-'} accent="emerald" />
      <StatCard label="Absences cumulees" value={stats.total_absences ?? 0} accent="rose" />
      <StatCard label="Evenements" value={stats.upcoming_events ?? 0} accent="amber" />
    </div>
  );
}
