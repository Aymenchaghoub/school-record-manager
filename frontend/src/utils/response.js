import { FALLBACK_PAGINATION } from './constants';

function normalizePagination(payload, itemsLength) {
  const currentPage =
    payload?.current_page ??
    payload?.meta?.current_page ??
    FALLBACK_PAGINATION.currentPage;

  const totalPages =
    payload?.last_page ??
    payload?.meta?.last_page ??
    FALLBACK_PAGINATION.totalPages;

  const total = payload?.total ?? payload?.meta?.total ?? itemsLength;

  return {
    currentPage,
    totalPages,
    total,
  };
}

export function parseListResponse(payload) {
  if (Array.isArray(payload)) {
    return {
      items: payload,
      pagination: normalizePagination(null, payload.length),
    };
  }

  if (Array.isArray(payload?.data)) {
    return {
      items: payload.data,
      pagination: normalizePagination(payload, payload.data.length),
    };
  }

  if (Array.isArray(payload?.items)) {
    return {
      items: payload.items,
      pagination: normalizePagination(payload, payload.items.length),
    };
  }

  return {
    items: [],
    pagination: { ...FALLBACK_PAGINATION },
  };
}

export function parseEntityResponse(payload) {
  if (payload?.data && !Array.isArray(payload.data)) {
    return payload.data;
  }

  return payload;
}
