import { Button } from '../ui/Button';

function DefaultIcon() {
  return (
    <svg
      viewBox="0 0 64 64"
      fill="none"
      xmlns="http://www.w3.org/2000/svg"
      className="h-16 w-16"
      style={{ color: 'var(--color-muted)' }}
    >
      <path
        d="M8 22C8 19.7909 9.79086 18 12 18H24L28 22H52C54.2091 22 56 23.7909 56 26V46C56 48.2091 54.2091 50 52 50H12C9.79086 50 8 48.2091 8 46V22Z"
        stroke="currentColor"
        strokeWidth="3"
        strokeLinecap="round"
        strokeLinejoin="round"
      />
      <path d="M20 34H44" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
      <path d="M20 40H36" stroke="currentColor" strokeWidth="3" strokeLinecap="round" />
    </svg>
  );
}

function renderIcon(icon) {
  if (typeof icon !== 'string' || !icon.trim()) {
    return <DefaultIcon />;
  }

  const trimmedIcon = icon.trim();

  if (trimmedIcon.startsWith('<svg')) {
    return (
      <span
        className="block h-16 w-16"
        style={{ color: 'var(--color-muted)' }}
        aria-hidden="true"
        dangerouslySetInnerHTML={{ __html: trimmedIcon }}
      />
    );
  }

  return (
    <span className="text-5xl leading-none" aria-hidden="true">
      {trimmedIcon}
    </span>
  );
}

export function EmptyState({
  icon,
  title,
  description,
  actionLabel,
  onAction,
}) {
  return (
    <div
      className="flex flex-col items-center justify-center rounded-2xl border px-6 py-12 text-center"
      style={{
        borderColor: 'var(--color-border)',
        backgroundColor: 'var(--color-surface)',
        color: 'var(--color-text)',
      }}
    >
      <div className="mb-4 flex h-16 w-16 items-center justify-center">{renderIcon(icon)}</div>
      <h3 className="text-xl font-bold" style={{ color: 'var(--color-text)' }}>
        {title}
      </h3>
      <p className="theme-muted mt-2 max-w-md text-sm">{description}</p>
      {actionLabel && onAction ? (
        <Button className="mt-6" onClick={onAction}>
          {actionLabel}
        </Button>
      ) : null}
    </div>
  );
}
