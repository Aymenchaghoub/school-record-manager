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
  style,
  ...props
}) {
  const resolvedVariant = variants[variant] || variants.primary;
  const resolvedStyle = resolvedVariant === variants.primary
    ? {
      background: 'linear-gradient(135deg, #E040A0, #A855F7)',
      boxShadow: '0 4px 14px rgba(168, 85, 247, 0.35)',
      ...(style || {}),
    }
    : style;

  return (
    <button
      type={type}
      disabled={disabled || isLoading}
      className={`ui-btn ${resolvedVariant} ${className}`}
      style={resolvedStyle}
      {...props}
    >
      {isLoading ? 'Chargement...' : children}
    </button>
  );
}
