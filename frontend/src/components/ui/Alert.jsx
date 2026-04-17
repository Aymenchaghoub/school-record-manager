const variants = {
  info: 'ui-alert-info',
  success: 'ui-alert-success',
  warning: 'ui-alert-warning',
  danger: 'ui-alert-danger',
};

export function Alert({ variant = 'info', children }) {
  const resolvedVariant = variants[variant] || variants.info;

  return (
    <div className={`ui-alert ${resolvedVariant}`}>{children}</div>
  );
}
