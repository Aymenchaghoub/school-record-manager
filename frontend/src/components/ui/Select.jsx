export function Select({ label, error, helperText, options = [], className = '', ...props }) {
  return (
    <label className="ds-field-label">
      {label ? <span className="font-medium">{label}</span> : null}
      <select
        className={`ds-select ${className}`}
        aria-invalid={Boolean(error)}
        {...props}
      >
        {options.map((option) => (
          <option key={option.value} value={option.value}>
            {option.label}
          </option>
        ))}
      </select>
      {error ? <span className="ds-field-error">{error}</span> : null}
      {!error && helperText ? <span className="ds-field-helper">{helperText}</span> : null}
    </label>
  );
}
