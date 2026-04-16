import { useEffect, useRef } from 'react';

export function Modal({ title, isOpen, onClose, children, footer }) {
  const modalRef = useRef(null);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    const firstFocusable = modalRef.current?.querySelector(
      'button, input, select, textarea, [tabindex]:not([tabindex="-1"])'
    );

    firstFocusable?.focus();
  }, [isOpen]);

  if (!isOpen) {
    return null;
  }

  return (
    <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 p-4">
      <div
        ref={modalRef}
        className="theme-modal-surface w-full max-w-2xl rounded-2xl border shadow-soft"
        role="dialog"
        aria-modal="true"
        aria-label={title}
      >
        <div className="theme-modal-divider flex items-center justify-between border-b px-5 py-4">
          <h3 className="text-lg font-semibold" style={{ color: 'var(--fg)' }}>
            {title}
          </h3>
          <button
            type="button"
            onClick={onClose}
            aria-label="Fermer la fenetre"
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
