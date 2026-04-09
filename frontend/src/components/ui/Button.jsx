const variants = {
  primary: 'bg-brand-600 text-white hover:bg-brand-700 focus:ring-brand-500',
  secondary: 'bg-white text-slate-700 border border-slate-300 hover:bg-slate-100 focus:ring-slate-400',
  danger: 'bg-red-600 text-white hover:bg-red-700 focus:ring-red-500',
  subtle: 'bg-slate-100 text-slate-700 hover:bg-slate-200 focus:ring-slate-400',
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
  return (
    <button
      type={type}
      disabled={disabled || isLoading}
      className={`inline-flex items-center justify-center rounded-xl px-4 py-2 text-sm font-semibold transition focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-60 ${variants[variant]} ${className}`}
      {...props}
    >
      {isLoading ? 'Chargement...' : children}
    </button>
  );
}
