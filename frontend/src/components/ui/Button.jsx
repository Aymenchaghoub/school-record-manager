const variants = {
  primary: 'ui-btn-primary',
  secondary: 'ui-btn-secondary',
  danger: 'ui-btn-danger',
  ghost: 'ui-btn-ghost',
  subtle: 'ui-btn-ghost',
};

export function Button({
  children,
  type = 'button',
  variant = 'primary',
  className = '',
  isLoading = false,
  disabled = false,
  ...props
}) {
  const resolvedVariant = variants[variant] || variants.primary;

  return (
    <button
      type={type}
      disabled={disabled || isLoading}
      className={`ui-btn ${resolvedVariant} ${className}`}
      {...props}
    >
      {isLoading ? 'Chargement...' : children}
    </button>
  );
}
