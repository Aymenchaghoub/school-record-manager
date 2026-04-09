const tones = {
  slate: 'bg-slate-100 text-slate-700 border-slate-200',
  brand: 'bg-cyan-100 text-cyan-800 border-cyan-200',
  success: 'bg-emerald-100 text-emerald-800 border-emerald-200',
  warning: 'bg-amber-100 text-amber-800 border-amber-200',
  danger: 'bg-red-100 text-red-800 border-red-200',
};

export function Badge({ tone = 'slate', children }) {
  return (
    <span className={`inline-flex rounded-full border px-2.5 py-1 text-xs font-semibold ${tones[tone]}`}>
      {children}
    </span>
  );
}
