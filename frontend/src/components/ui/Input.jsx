export function Input({ label, error, className = '', ...props }) {
  return (
    <label className="flex w-full flex-col gap-1 text-sm text-slate-700">
      {label ? <span className="font-medium">{label}</span> : null}
      <input
        className={`w-full rounded-xl border border-slate-300 bg-white px-3 py-2 text-sm text-slate-800 outline-none transition focus:border-brand-500 focus:ring-2 focus:ring-brand-200 ${className}`}
        {...props}
      />
      {error ? <span className="text-xs text-red-600">{error}</span> : null}
    </label>
  );
}
