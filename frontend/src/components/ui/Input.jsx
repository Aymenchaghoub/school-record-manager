import FR from '../../i18n/fr';

function getLiveEmailError(type, value) {
  if (type !== 'email') {
    return '';
  }

  const text = String(value ?? '').trim();
  if (text === '') {
    return '';
  }

  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (emailRegex.test(text)) {
    return '';
  }

  return FR.common.errors.emailInvalid;
}

export function Input({ label, error, helperText, className = '', ...props }) {
  const liveEmailError = getLiveEmailError(props.type, props.value);
  const displayError = error || liveEmailError;

  return (
    <label className="ds-field-label">
      {label ? <span className="font-medium">{label}</span> : null}
      <input
        className={`ds-input ${className}`}
        aria-invalid={Boolean(displayError)}
        {...props}
      />
      {displayError ? <span className="ds-field-error">{displayError}</span> : null}
      {!displayError && helperText ? <span className="ds-field-helper">{helperText}</span> : null}
    </label>
  );
}
