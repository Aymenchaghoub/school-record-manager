import { StatCard } from '../../components/common/StatCard';

export function AdminDashboard({ payload }) {
  const stats = payload?.stats || payload || {};

  return (
    <div className="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
      <StatCard label="Etudiants" value={stats.total_students ?? 0} accent="cyan" />
      <StatCard label="Enseignants" value={stats.total_teachers ?? 0} accent="emerald" />
      <StatCard label="Classes" value={stats.total_classes ?? 0} accent="amber" />
      <StatCard label="Evenements a venir" value={stats.upcoming_events ?? 0} accent="rose" />
    </div>
  );
}
