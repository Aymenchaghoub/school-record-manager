import { StatCard } from '../../components/common/StatCard';

export function StudentDashboard({ payload }) {
  const stats = payload?.stats || payload || {};

  return (
    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Moyenne" value={stats.overall_average ?? stats.gpa ?? 0} accent="cyan" />
      <StatCard label="Absences" value={stats.total_absences ?? 0} accent="rose" />
      <StatCard label="Taux presence" value={`${stats.attendance_rate ?? 0}%`} accent="emerald" />
      <StatCard label="Rang classe" value={stats.class_rank ?? '-'} accent="amber" />
    </div>
  );
}
