export function StatCard({ label, value, accent = 'cyan', delta, icon = null }) {
  const accents = {
    cyan: '#A855F7',
    emerald: '#22C55E',
    amber: '#F59E0B',
    rose: '#EF4444',
  };

  const accentColor = accents[accent] || accents.cyan;

  let deltaValue = null;
  if (delta !== undefined && delta !== null && String(delta).trim() !== '') {
    const parsedDelta = Number(delta);
    if (Number.isFinite(parsedDelta)) {
      deltaValue = parsedDelta;
    }
  }

  const deltaIsPositive = deltaValue === null ? true : deltaValue >= 0;
  const deltaText = deltaValue === null
    ? ''
    : `${deltaIsPositive ? '+' : ''}${deltaValue.toFixed(1)}%`;

  return (
    <div className="kpi-card">
      <div className="mb-2 flex items-start justify-between gap-2">
        <p className="kpi-label">{label}</p>
        {icon ? (
          <span
            className="inline-flex h-6 w-6 items-center justify-center rounded-md"
            style={{ color: accentColor }}
            aria-hidden="true"
          >
            {icon}
          </span>
        ) : null}
      </div>

      <p className="kpi-value">{value}</p>

      {deltaText ? (
        <p className={`kpi-delta ${deltaIsPositive ? 'kpi-delta-positive' : 'kpi-delta-negative'}`}>
          {deltaText}
        </p>
      ) : null}
    </div>
  );
}
