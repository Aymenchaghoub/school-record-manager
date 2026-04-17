import { useEffect, useRef } from 'react';

export function Modal({ title, isOpen, onClose, children, footer }) {
  const modalRef = useRef(null);
  const closeButtonRef = useRef(null);
  const previousFocusRef = useRef(null);

  useEffect(() => {
    if (!isOpen) {
      return;
    }

    previousFocusRef.current = document.activeElement instanceof HTMLElement ? document.activeElement : null;

    const getFocusableElements = () => {
      const panel = modalRef.current;
      if (!panel) {
        return [];
      }

      const candidates = panel.querySelectorAll(
        'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
      );

      return Array.from(candidates).filter(
        (node) => node instanceof HTMLElement && !node.hasAttribute('disabled')
      );
    };

    const initialFocusable = getFocusableElements();
    const firstField = initialFocusable.find((element) =>
      ['INPUT', 'SELECT', 'TEXTAREA'].includes(element.tagName)
    );
    (firstField || initialFocusable[0] || closeButtonRef.current)?.focus();

    const handleKeyDown = (event) => {
      if (event.key === 'Escape') {
        event.preventDefault();
        onClose?.();
        return;
      }

      if (event.key !== 'Tab') {
        return;
      }

      const focusable = getFocusableElements();
      if (focusable.length === 0) {
        event.preventDefault();
        return;
      }

      const first = focusable[0];
      const last = focusable[focusable.length - 1];
      const active = document.activeElement;

      if (event.shiftKey && active === first) {
        event.preventDefault();
        last.focus();
      } else if (!event.shiftKey && active === last) {
        event.preventDefault();
        first.focus();
      }
    };

    document.addEventListener('keydown', handleKeyDown);

    return () => {
      document.removeEventListener('keydown', handleKeyDown);
      previousFocusRef.current?.focus?.();
    };
  }, [isOpen, onClose]);

  if (!isOpen) {
    return null;
  }

  return (
    <div className="theme-modal-overlay" onMouseDown={onClose}>
      <div
        ref={modalRef}
        className="theme-modal-surface"
        role="dialog"
        aria-modal="true"
        aria-label={title}
        onMouseDown={(event) => event.stopPropagation()}
      >
        <div className="theme-modal-divider flex items-center justify-between border-b px-5 py-4">
          <h2 className="text-[22px] font-semibold" style={{ color: 'var(--color-text)' }}>
            {title}
          </h2>
          <button
            ref={closeButtonRef}
            type="button"
            onClick={onClose}
            aria-label="Fermer la fenetre"
            className="theme-modal-close rounded-md px-2 py-1 transition"
          >
            &times;
          </button>
        </div>

        <div className="max-h-[65vh] overflow-y-auto px-5 py-4">{children}</div>

        {footer ? <div className="theme-modal-divider border-t px-5 py-4">{footer}</div> : null}
      </div>
    </div>
  );
}
