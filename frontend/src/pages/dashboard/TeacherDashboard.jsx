import { StatCard } from '../../components/common/StatCard';

export function TeacherDashboard({ payload }) {
  const stats = payload?.stats || payload || {};

  return (
    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Classes" value={stats.total_classes ?? 0} accent="cyan" />
      <StatCard label="Matieres" value={stats.total_subjects ?? 0} accent="emerald" />
      <StatCard label="Eleves" value={stats.total_students ?? 0} accent="amber" />
      <StatCard label="Notes cette semaine" value={stats.grades_this_week ?? 0} accent="rose" />
    </div>
  );
}
