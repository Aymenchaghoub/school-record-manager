import { useEffect, useMemo, useState } from 'react';
import toast from 'react-hot-toast';
import { useDebounce } from '../../hooks/useDebounce';
import { parseListResponse } from '../../utils/response';
import { formatDate, formatDateTime, toBoolean } from '../../utils/format';
import { Alert } from '../ui/Alert';
import { Badge } from '../ui/Badge';
import { Button } from '../ui/Button';
import { Input } from '../ui/Input';
import { Modal } from '../ui/Modal';
import { Select } from '../ui/Select';
import { Spinner } from '../ui/Spinner';
import { Textarea } from '../ui/Textarea';
import { ConfirmModal } from './ConfirmModal';
import { EmptyState } from './EmptyState';
import { Pagination } from './Pagination';
import { PageHeader } from './PageHeader';

function buildInitialFilterValues(filters) {
  return filters.reduce((acc, filter) => {
    acc[filter.name] = filter.defaultValue ?? '';
    return acc;
  }, {});
}

function compactParams(params) {
  return Object.fromEntries(
    Object.entries(params).filter(([, value]) => value !== undefined && value !== null && value !== '')
  );
}

function buildEmptyValues(fields) {
  return fields.reduce((acc, field) => {
    if (field.type === 'checkbox') {
      acc[field.name] = Boolean(field.defaultValue);
    } else {
      acc[field.name] = field.defaultValue ?? '';
    }

    return acc;
  }, {});
}

function formatCellValue(value, format) {
  if (format === 'date') {
    return formatDate(value);
  }

  if (format === 'datetime') {
    return formatDateTime(value);
  }

  if (typeof value === 'boolean') {
    return <Badge tone={value ? 'success' : 'danger'}>{value ? 'Oui' : 'Non'}</Badge>;
  }

  if (value === null || value === undefined || value === '') {
    return '-';
  }

  return String(value);
}

export function CrudPage({
  title,
  description,
  service,
  columns,
  fields,
  idKey = 'id',
  canCreate = true,
  canEdit = true,
  canDelete = true,
  createLabel = 'Nouveau',
  mapItemToForm,
  mapFormToPayload,
  emptyState,
  searchEnabled = true,
  searchPlaceholder = 'Recherche...',
  searchDebounceMs = 0,
  filters = [],
  buildListParams,
  onListLoaded,
}) {
  const [items, setItems] = useState([]);
  const [pagination, setPagination] = useState({ currentPage: 1, totalPages: 1, total: 0 });
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [search, setSearch] = useState('');
  const [filterValues, setFilterValues] = useState(() => buildInitialFilterValues(filters));
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [formValues, setFormValues] = useState(buildEmptyValues(fields));
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [deleteTargetId, setDeleteTargetId] = useState(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const effectiveSearch = searchDebounceMs > 0 ? useDebounce(search, searchDebounceMs) : search;

  const modalTitle = editingItem ? `Modifier ${title}` : `Creer ${title}`;

  const handleFilterChange = (name, value) => {
    setPagination((prev) => ({ ...prev, currentPage: 1 }));
    setFilterValues((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const loadItems = useMemo(
    () => async () => {
      setIsLoading(true);
      setError('');

      try {
        const cleanSearch = String(effectiveSearch || '').trim();
        const defaultParams = {
          page: pagination.currentPage,
          ...(searchEnabled ? { search: cleanSearch } : {}),
          ...filterValues,
        };

        const params = compactParams(
          buildListParams
            ? buildListParams({
              search: cleanSearch,
              page: pagination.currentPage,
              filters: filterValues,
            })
            : defaultParams
        );

        const payload = await service.list(params);
        const listPayload = payload?.data || payload;
        onListLoaded?.(listPayload);

        const parsed = parseListResponse(listPayload);
        setItems(parsed.items);
        setPagination(parsed.pagination);
      } catch (err) {
        setError(err?.response?.data?.message || err?.message || 'Erreur de chargement');
      } finally {
        setIsLoading(false);
      }
    },
    [
      service,
      effectiveSearch,
      searchEnabled,
      pagination.currentPage,
      filterValues,
      buildListParams,
      onListLoaded,
    ]
  );

  useEffect(() => {
    loadItems();
  }, [loadItems]);

  const resetForm = () => {
    setEditingItem(null);
    setFormValues(buildEmptyValues(fields));
  };

  const openCreateModal = () => {
    resetForm();
    setError('');
    setIsModalOpen(true);
  };

  const openEditModal = (item) => {
    const values = mapItemToForm ? mapItemToForm(item) : item;
    const nextValues = buildEmptyValues(fields);

    fields.forEach((field) => {
      if (values[field.name] !== undefined) {
        nextValues[field.name] = field.type === 'checkbox' ? toBoolean(values[field.name]) : values[field.name];
      }
    });

    setEditingItem(item);
    setFormValues(nextValues);
    setError('');
    setIsModalOpen(true);
  };

  const closeModal = () => {
    setIsModalOpen(false);
    resetForm();
  };

  const handleChange = (name, value) => {
    setFormValues((prev) => ({
      ...prev,
      [name]: value,
    }));
  };

  const submitForm = async (event) => {
    event.preventDefault();
    setIsSubmitting(true);
    setError('');
    setSuccessMessage('');

    const loadingToastId = toast.loading(
      editingItem ? 'Updating record...' : 'Creating record...'
    );

    try {
      const payload = mapFormToPayload ? mapFormToPayload(formValues) : formValues;

      if (editingItem) {
        await service.update(editingItem[idKey], payload);
        setSuccessMessage('Modification enregistree avec succes.');
      } else {
        await service.create(payload);
        setSuccessMessage('Creation enregistree avec succes.');
      }

      closeModal();
      await loadItems();
    } catch (err) {
      setError(err?.response?.data?.message || err?.message || 'Erreur pendant la sauvegarde');
    } finally {
      toast.dismiss(loadingToastId);
      setIsSubmitting(false);
    }
  };

  const openDeleteConfirm = (itemId) => {
    setDeleteTargetId(itemId);
    setIsConfirmOpen(true);
  };

  const closeDeleteConfirm = () => {
    if (isDeleting) {
      return;
    }

    setIsConfirmOpen(false);
    setDeleteTargetId(null);
  };

  const confirmDelete = async () => {
    if (!deleteTargetId) {
      closeDeleteConfirm();
      return;
    }

    try {
      setIsDeleting(true);
      setError('');
      setSuccessMessage('');
      await service.remove(deleteTargetId);
      setSuccessMessage('Suppression effectuee avec succes.');
      setIsConfirmOpen(false);
      setDeleteTargetId(null);
      await loadItems();
    } catch (err) {
      setError(err?.response?.data?.message || err?.message || 'Suppression impossible');
    } finally {
      setIsDeleting(false);
    }
  };

  return (
    <div className="space-y-5">
      <PageHeader
        title={title}
        description={description}
        action={
          canCreate ? (
            <Button onClick={openCreateModal}>
              + {createLabel}
            </Button>
          ) : null
        }
      />

      <div className="surface-card p-4">
        <div className="mb-4 flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
          {searchEnabled ? (
            <Input
              placeholder={searchPlaceholder}
              value={search}
              onChange={(event) => {
                setPagination((prev) => ({ ...prev, currentPage: 1 }));
                setSearch(event.target.value);
              }}
              className="md:max-w-sm"
            />
          ) : (
            <div />
          )}
          <Button variant="secondary" onClick={loadItems}>
            Rafraichir
          </Button>
        </div>

        {filters.length > 0 ? (
          <div className="mb-4 grid gap-3 md:grid-cols-2 xl:grid-cols-3">
            {filters.map((filter) => (
              <Select
                key={filter.name}
                label={filter.label}
                value={filterValues[filter.name] ?? ''}
                onChange={(event) => handleFilterChange(filter.name, event.target.value)}
                options={filter.options || []}
              />
            ))}
          </div>
        ) : null}

        {error ? <Alert variant="danger">{error}</Alert> : null}
        {successMessage ? <Alert variant="success">{successMessage}</Alert> : null}

        {isLoading ? (
          <div className="py-6">
            <Spinner label="Chargement des donnees..." />
          </div>
        ) : items.length === 0 ? (
          <EmptyState
            icon={emptyState?.icon}
            title={emptyState?.title || 'No results found'}
            description={
              emptyState?.description || 'Try adjusting your search or create a new record.'
            }
            actionLabel={canCreate ? emptyState?.actionLabel : undefined}
            onAction={canCreate ? (emptyState?.onAction || openCreateModal) : undefined}
          />
        ) : (
          <div className="overflow-x-auto">
            <table className="theme-table min-w-full text-left text-sm">
              <thead>
                <tr className="border-b">
                  {columns.map((column) => (
                    <th key={column.key} className="px-3 py-2 font-semibold">
                      {column.label}
                    </th>
                  ))}
                  {(canEdit || canDelete) ? <th className="px-3 py-2 font-semibold">Actions</th> : null}
                </tr>
              </thead>
              <tbody>
                {items.map((item) => (
                  <tr key={item[idKey]} className="border-b">
                    {columns.map((column) => (
                      <td key={column.key} className="px-3 py-3 align-top">
                        {column.render
                          ? column.render(item)
                          : formatCellValue(item[column.key], column.format)}
                      </td>
                    ))}

                    {(canEdit || canDelete) ? (
                      <td className="px-3 py-3">
                        <div className="flex gap-2">
                          {canEdit ? (
                            <Button variant="subtle" onClick={() => openEditModal(item)}>
                              Editer
                            </Button>
                          ) : null}
                          {canDelete ? (
                            <Button variant="danger" onClick={() => openDeleteConfirm(item[idKey])}>
                              Supprimer
                            </Button>
                          ) : null}
                        </div>
                      </td>
                    ) : null}
                  </tr>
                ))}
              </tbody>
            </table>
          </div>
        )}

        <div className="theme-muted mt-4 space-y-3 text-sm">
          <p>Total: {pagination.total}</p>
          <Pagination
            currentPage={pagination.currentPage}
            lastPage={Math.max(1, pagination.totalPages)}
            onPageChange={(page) =>
              setPagination((prev) => ({
                ...prev,
                currentPage: page,
              }))
            }
          />
        </div>
      </div>

      <Modal
        title={modalTitle}
        isOpen={isModalOpen}
        onClose={closeModal}
        footer={
          <div className="flex justify-end gap-2">
            <Button variant="secondary" onClick={closeModal}>
              Annuler
            </Button>
            <Button type="submit" form="crud-form" isLoading={isSubmitting}>
              Enregistrer
            </Button>
          </div>
        }
      >
        <form id="crud-form" className="grid gap-4 md:grid-cols-2" onSubmit={submitForm}>
          {fields.map((field) => {
            if (field.type === 'select') {
              return (
                <Select
                  key={field.name}
                  label={field.label}
                  value={formValues[field.name] ?? ''}
                  onChange={(event) => handleChange(field.name, event.target.value)}
                  options={field.options || []}
                  required={field.required}
                />
              );
            }

            if (field.type === 'textarea') {
              return (
                <div key={field.name} className="md:col-span-2">
                  <Textarea
                    label={field.label}
                    rows={4}
                    value={formValues[field.name] ?? ''}
                    onChange={(event) => handleChange(field.name, event.target.value)}
                    required={field.required}
                  />
                </div>
              );
            }

            if (field.type === 'checkbox') {
              return (
                <label key={field.name} className="flex items-center gap-2 rounded-xl border border-slate-200 px-3 py-2">
                  <input
                    type="checkbox"
                    checked={Boolean(formValues[field.name])}
                    onChange={(event) => handleChange(field.name, event.target.checked)}
                  />
                  <span className="text-sm text-slate-700">{field.label}</span>
                </label>
              );
            }

            return (
              <Input
                key={field.name}
                label={field.label}
                type={field.type || 'text'}
                value={formValues[field.name] ?? ''}
                onChange={(event) => handleChange(field.name, event.target.value)}
                required={field.required}
              />
            );
          })}
        </form>
      </Modal>

      <ConfirmModal
        isOpen={isConfirmOpen}
        title="Confirm deletion"
        message="Are you sure you want to delete this record? This action cannot be undone."
        confirmLabel="Delete"
        onConfirm={confirmDelete}
        onCancel={closeDeleteConfirm}
        danger
        isConfirming={isDeleting}
      />
    </div>
  );
}
