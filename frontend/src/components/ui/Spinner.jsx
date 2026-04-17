export function Spinner({ label = 'Chargement...' }) {
  return (
    <div className="ui-spinner" role="status" aria-live="polite">
      <span className="ui-spinner-dot" aria-hidden="true" />
      <span>{label}</span>
    </div>
  );
}
