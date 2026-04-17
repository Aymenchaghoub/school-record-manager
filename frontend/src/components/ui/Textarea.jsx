export function Textarea({ label, error, className = '', ...props }) {
  return (
    <label className="ds-field-label">
      {label ? <span className="font-medium">{label}</span> : null}
      <textarea
        className={`ds-textarea ${className}`}
        {...props}
      />
      {error ? <span className="ds-field-error">{error}</span> : null}
    </label>
  );
}
