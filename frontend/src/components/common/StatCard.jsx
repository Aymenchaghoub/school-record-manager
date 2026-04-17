export function StatCard({ label, value, accent = 'cyan', delta }) {
  const accents = {
    cyan: '#A855F7',
    emerald: '#22C55E',
    amber: '#F59E0B',
    rose: '#EF4444',
  };

  const accentColor = accents[accent] || accents.cyan;
  const deltaIsPositive = typeof delta === 'string' ? !delta.trim().startsWith('-') : Boolean(delta >= 0);

  return (
    <div className="kpi-card">
      <div className="mb-3 h-1.5 w-16 rounded-full" style={{ backgroundColor: accentColor }} />
      <p className="kpi-label">{label}</p>
      <p className="kpi-value">
        {value}
      </p>
      {delta !== undefined && delta !== null && String(delta) !== '' ? (
        <p className={`kpi-delta ${deltaIsPositive ? 'kpi-delta-positive' : 'kpi-delta-negative'}`}>
          {deltaIsPositive ? '+' : ''}
          {delta}
        </p>
      ) : null}
    </div>
  );
}
