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
        Page {safeCurrentPage} of {safeLastPage}
      </p>

      <div className="flex items-center gap-2">
        <Button
          variant="secondary"
          disabled={safeCurrentPage <= 1}
          onClick={() => handlePageChange(safeCurrentPage - 1)}
        >
          Previous
        </Button>

        {visiblePages.map((page) => (
          <Button
            key={page}
            variant={page === safeCurrentPage ? 'primary' : 'secondary'}
            className="min-w-[2.5rem]"
            onClick={() => handlePageChange(page)}
          >
            {page}
          </Button>
        ))}

        <Button
          variant="secondary"
          disabled={safeCurrentPage >= safeLastPage}
          onClick={() => handlePageChange(safeCurrentPage + 1)}
        >
          Next
        </Button>
      </div>
    </div>
  );
}
