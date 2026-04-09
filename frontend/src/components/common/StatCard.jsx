export function StatCard({ label, value, accent = 'cyan' }) {
  const accents = {
    cyan: 'from-cyan-500/20 to-cyan-200/30 text-cyan-700',
    emerald: 'from-emerald-500/20 to-emerald-200/30 text-emerald-700',
    amber: 'from-amber-500/20 to-amber-200/30 text-amber-700',
    rose: 'from-rose-500/20 to-rose-200/30 text-rose-700',
  };

  return (
    <div className="surface-card p-4">
      <div className={`mb-3 h-2 w-20 rounded-full bg-gradient-to-r ${accents[accent]}`} />
      <p className="theme-muted text-sm font-medium">{label}</p>
      <p className="mt-2 text-2xl font-bold" style={{ color: 'var(--fg)' }}>
        {value}
      </p>
    </div>
  );
}
