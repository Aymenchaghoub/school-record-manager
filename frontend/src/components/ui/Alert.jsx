const variants = {
  info: 'border-cyan-200 bg-cyan-50 text-cyan-800',
  success: 'border-emerald-200 bg-emerald-50 text-emerald-800',
  warning: 'border-amber-200 bg-amber-50 text-amber-800',
  danger: 'border-red-200 bg-red-50 text-red-800',
};

export function Alert({ variant = 'info', children }) {
  return (
    <div className={`rounded-xl border px-4 py-3 text-sm ${variants[variant]}`}>{children}</div>
  );
}
