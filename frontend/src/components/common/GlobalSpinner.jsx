export function GlobalSpinner() {
  return (
    <div className="global-spinner-overlay" role="status" aria-live="polite" aria-label="Loading">
      <div className="global-spinner-circle" />
    </div>
  );
}
