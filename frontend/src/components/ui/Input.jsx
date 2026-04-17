export function Input({ label, error, className = '', ...props }) {
  return (
    <label className="ds-field-label">
      {label ? <span className="font-medium">{label}</span> : null}
      <input
        className={`ds-input ${className}`}
        {...props}
      />
      {error ? <span className="ds-field-error">{error}</span> : null}
    </label>
  );
}
