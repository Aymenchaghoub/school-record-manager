import { useEffect } from 'react';
import { Button } from '../ui/Button';

export function ConfirmModal({
  isOpen,
  title,
  message,
  confirmLabel,
  onConfirm,
  onCancel,
  danger = false,
  isConfirming = false,
}) {
  useEffect(() => {
    if (!isOpen) {
      return undefined;
    }

    const handleKeyDown = (event) => {
      if (event.key === 'Escape') {
        onCancel?.();
      }
    };

    window.addEventListener('keydown', handleKeyDown);

    return () => {
      window.removeEventListener('keydown', handleKeyDown);
    };
  }, [isOpen, onCancel]);

  return (
    <div
      className={`confirm-modal-overlay ${isOpen ? 'confirm-modal-overlay-open' : ''}`}
      onClick={onCancel}
      aria-hidden={!isOpen}
    >
      <div
        className={`confirm-modal-panel ${isOpen ? 'confirm-modal-panel-open' : ''}`}
        onClick={(event) => event.stopPropagation()}
        role="dialog"
        aria-modal="true"
        aria-labelledby="confirm-modal-title"
      >
        <h2 id="confirm-modal-title" className="text-lg font-bold" style={{ color: 'var(--fg)' }}>
          {title}
        </h2>
        <p className="theme-muted mt-2 text-sm">{message}</p>

        <div className="mt-6 flex justify-end gap-2">
          <Button variant="secondary" onClick={onCancel} disabled={isConfirming}>
            Annuler
          </Button>
          <Button
            variant={danger ? 'danger' : 'primary'}
            onClick={onConfirm}
            isLoading={isConfirming}
          >
            {confirmLabel || 'Confirmer'}
          </Button>
        </div>
      </div>
    </div>
  );
}
