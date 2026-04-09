export function Modal({ title, isOpen, onClose, children, footer }) {
  if (!isOpen) {
    return null;
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
      <div className="theme-modal-surface w-full max-w-2xl rounded-2xl border shadow-soft">
        <div className="theme-modal-divider flex items-center justify-between border-b px-5 py-4">
          <h3 className="text-lg font-semibold" style={{ color: 'var(--fg)' }}>
            {title}
          </h3>
          <button
            type="button"
            onClick={onClose}
            className="theme-modal-close rounded-md px-2 py-1 transition"
          >
            x
          </button>
        </div>

        <div className="max-h-[65vh] overflow-y-auto px-5 py-4">{children}</div>

        {footer ? <div className="theme-modal-divider border-t px-5 py-4">{footer}</div> : null}
      </div>
    </div>
  );
}
