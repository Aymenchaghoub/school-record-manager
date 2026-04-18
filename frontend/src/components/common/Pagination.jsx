import { Button } from '../ui/Button';

function buildVisiblePages(currentPage, lastPage, maxVisible = 5) {
  if (lastPage <= maxVisible) {
    return Array.from({ length: lastPage }, (_, index) => index + 1);
  }

  const half = Math.floor(maxVisible / 2);
  let start = Math.max(1, currentPage - half);
  let end = start + maxVisible - 1;

  if (end > lastPage) {
    end = lastPage;
    start = Math.max(1, end - maxVisible + 1);
  }

  return Array.from({ length: end - start + 1 }, (_, index) => start + index);
}

export function Pagination({ currentPage = 1, lastPage = 1, onPageChange }) {
  const safeCurrentPage = Math.max(1, currentPage);
  const safeLastPage = Math.max(1, lastPage);
  const visiblePages = buildVisiblePages(safeCurrentPage, safeLastPage, 5);

  const handlePageChange = (page) => {
    if (page < 1 || page > safeLastPage || page === safeCurrentPage) {
      return;
    }

    onPageChange?.(page);
  };

  return (
    <div className="theme-muted mt-4 flex flex-col gap-3 text-sm md:flex-row md:items-center md:justify-between">
      <p>
        Page {safeCurrentPage} sur {safeLastPage}
      </p>

      <div className="flex items-center gap-2">
        <Button
          variant="ghost"
          disabled={safeCurrentPage <= 1}
          onClick={() => handlePageChange(safeCurrentPage - 1)}
        >
          Precedent
        </Button>

        {visiblePages.map((page) => (
          <Button
            key={page}
            variant="ghost"
            className="min-w-[2.5rem]"
            style={
              page === safeCurrentPage
                ? {
                  color: 'var(--color-primary)',
                  fontWeight: 600,
                  border: '1px solid color-mix(in srgb, var(--color-primary) 35%, transparent)',
                }
                : undefined
            }
            onClick={() => handlePageChange(page)}
          >
            {page}
          </Button>
        ))}

        <Button
          variant="ghost"
          disabled={safeCurrentPage >= safeLastPage}
          onClick={() => handlePageChange(safeCurrentPage + 1)}
        >
          Suivant
        </Button>
      </div>
    </div>
  );
}
