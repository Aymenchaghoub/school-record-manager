export function Textarea({ label, error, helperText, className = '', ...props }) {
  return (
    <label className="ds-field-label">
      {label ? <span className="font-medium">{label}</span> : null}
      <textarea
        className={`ds-textarea ${className}`}
        aria-invalid={Boolean(error)}
        {...props}
      />
      {error ? <span className="ds-field-error">{error}</span> : null}
      {!error && helperText ? <span className="ds-field-helper">{helperText}</span> : null}
    </label>
  );
}
