export function PageHeader({ title, description, action }) {
  return (
    <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
      <div>
        <h1 style={{ fontSize: 'var(--font-size-h1)', fontWeight: 700, color: 'var(--color-text)' }}>{title}</h1>
        {description ? (
          <p className="mt-1" style={{ fontSize: '13px', color: 'var(--color-muted)' }}>
            {description}
          </p>
        ) : null}
      </div>
      {action ? <div>{action}</div> : null}
    </div>
  );
}
