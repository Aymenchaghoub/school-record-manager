import { useEffect, useMemo, useState } from 'react';
import toast from 'react-hot-toast';
import { useDebounce } from '../../hooks/useDebounce';
import { parseListResponse } from '../../utils/response';
import { formatDate, formatDateTime, toBoolean } from '../../utils/format';
import FR from '../../i18n/fr';
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
      return acc;
    }

    if (field.type === 'multiselect') {
      acc[field.name] = Array.isArray(field.defaultValue) ? field.defaultValue : [];
      return acc;
    }

    acc[field.name] = field.defaultValue ?? '';
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
    return (
      <Badge tone={value ? 'success' : 'danger'}>
        {value ? FR.common.labels.yes : FR.common.labels.no}
      </Badge>
    );
  }

  if (value === null || value === undefined || value === '') {
    return '-';
  }

  return String(value);
}

function inferFieldPlaceholder(field) {
  if (field.placeholder) {
    return field.placeholder;
  }

  const fieldLabel = String(field.label || field.name || '').toLowerCase();

  if (field.type === 'email') {
    return 'ex: nom@ecole.fr';
  }

  if (field.type === 'date') {
    return 'jj/mm/aaaa';
  }

  if (field.type === 'datetime-local') {
    return 'Choisissez une date et heure';
  }

  if (field.type === 'time') {
    return 'hh:mm';
  }

  if (fieldLabel.includes('classe')) {
    return 'ex: 3eme B';
  }

  if (fieldLabel.includes('matiere')) {
    return 'ex: Mathematiques';
  }

  if (fieldLabel.includes('note') || fieldLabel.includes('moyenne') || fieldLabel.includes('valeur')) {
    return 'ex: 14.5';
  }

  if (fieldLabel.includes('coefficient')) {
    return 'ex: 2';
  }

  if (fieldLabel.includes('nom')) {
    return 'ex: Lea Martin';
  }

  if (fieldLabel.includes('telephone')) {
    return 'ex: +212600000000';
  }

  if (fieldLabel.includes('email')) {
    return 'ex: nom@ecole.fr';
  }

  return '';
}

function inferFieldHelperText(field) {
  if (field.helperText) {
    return field.helperText;
  }

  const fieldLabel = String(field.label || field.name || '').toLowerCase();

  if (fieldLabel.includes('coefficient')) {
    return 'Entre 1 et 5';
  }

  if ((fieldLabel.includes('note') || fieldLabel.includes('moyenne') || fieldLabel.includes('valeur')) && field.type === 'number') {
    return 'Note sur 20';
  }

  return '';
}

function buildRequiredFieldMessage(field, placeholder) {
  const label = String(field.label || field.name || 'ce champ').toLowerCase();

  if (field.type === 'select' || field.type === 'multiselect') {
    return `Selectionnez ${label}.`;
  }

  if (placeholder && placeholder.startsWith('ex:')) {
    return `Entrez ${label}, ${placeholder}.`;
  }

  return `Renseignez ${label}.`;
}

function isFieldEmpty(field, value) {
  if (field.type === 'checkbox') {
    return value !== true;
  }

  if (field.type === 'multiselect') {
    return !Array.isArray(value) || value.length === 0;
  }

  if (Array.isArray(value)) {
    return value.length === 0;
  }

  return value === null || value === undefined || String(value).trim() === '';
}

function resolveRowAccessibleName(item, idKey) {
  return (
    item?.name
    || item?.title
    || item?.student?.name
    || item?.student_name
    || item?.email
    || item?.class?.name
    || item?.class_name
    || `#${item?.[idKey]}`
  );
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
  applyClientFilters,
  includeFiltersInRequest = true,
}) {
  const [items, setItems] = useState([]);
  const [sourceItems, setSourceItems] = useState([]);
  const [pagination, setPagination] = useState({ currentPage: 1, totalPages: 1, total: 0 });
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');
  const [successMessage, setSuccessMessage] = useState('');
  const [search, setSearch] = useState('');
  const [filterValues, setFilterValues] = useState(() => buildInitialFilterValues(filters));
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [formValues, setFormValues] = useState(buildEmptyValues(fields));
  const [formErrors, setFormErrors] = useState({});
  const [isSubmitting, setIsSubmitting] = useState(false);
  const [isConfirmOpen, setIsConfirmOpen] = useState(false);
  const [deleteTargetId, setDeleteTargetId] = useState(null);
  const [isDeleting, setIsDeleting] = useState(false);
  const effectiveSearch = searchDebounceMs > 0 ? useDebounce(search, searchDebounceMs) : search;

  const requestFilterSignature = includeFiltersInRequest ? JSON.stringify(filterValues) : '';
  const modalTitle = editingItem
    ? `${FR.common.actions.edit} ${title}`
    : `${FR.common.actions.create} ${title}`;

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
        const requestFilters = includeFiltersInRequest ? filterValues : {};

        const defaultParams = {
          page: pagination.currentPage,
          ...(searchEnabled ? { search: cleanSearch } : {}),
          ...requestFilters,
        };

        const params = compactParams(
          buildListParams
            ? buildListParams({
              search: cleanSearch,
              page: pagination.currentPage,
              filters: requestFilters,
            })
            : defaultParams
        );

        const payload = await service.list(params);
        const listPayload = payload?.data || payload;
        onListLoaded?.(listPayload);

        const parsed = parseListResponse(listPayload);
        const loadedItems = parsed.items;
        const filteredItems = typeof applyClientFilters === 'function'
          ? applyClientFilters(loadedItems, filterValues)
          : loadedItems;

        setSourceItems(loadedItems);
        setItems(filteredItems);

        if (typeof applyClientFilters === 'function') {
          setPagination({ currentPage: 1, totalPages: 1, total: filteredItems.length });
        } else {
          setPagination(parsed.pagination);
        }
      } catch (err) {
        setError(err?.response?.data?.message || err?.message || FR.common.errors.load);
      } finally {
        setIsLoading(false);
      }
    },
    [
      service,
      effectiveSearch,
      searchEnabled,
      pagination.currentPage,
      requestFilterSignature,
      includeFiltersInRequest,
      buildListParams,
      onListLoaded,
      applyClientFilters,
    ]
  );

  useEffect(() => {
    loadItems();
  }, [loadItems]);

  useEffect(() => {
    if (typeof applyClientFilters !== 'function') {
      return;
    }

    const filteredItems = applyClientFilters(sourceItems, filterValues);
    setItems(filteredItems);
    setPagination((prev) => ({ ...prev, currentPage: 1, totalPages: 1, total: filteredItems.length }));
  }, [applyClientFilters, sourceItems, filterValues]);

  const resetForm = () => {
    setEditingItem(null);
    setFormValues(buildEmptyValues(fields));
    setFormErrors({});
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
      if (values[field.name] === undefined) {
        return;
      }

      if (field.type === 'checkbox') {
        nextValues[field.name] = toBoolean(values[field.name]);
        return;
      }

      if (field.type === 'multiselect') {
        nextValues[field.name] = Array.isArray(values[field.name]) ? values[field.name].map(String) : [];
        return;
      }

      nextValues[field.name] = values[field.name];
    });

    setEditingItem(item);
    setFormValues(nextValues);
    setFormErrors({});
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

    setFormErrors((prev) => {
      if (!prev[name]) {
        return prev;
      }

      const nextErrors = { ...prev };
      delete nextErrors[name];
      return nextErrors;
    });
  };

  const validateForm = () => {
    const nextErrors = {};

    const visibleFields = fields.filter((field) => !field.hiddenWhen || !field.hiddenWhen(formValues));

    visibleFields.forEach((field) => {
      const value = formValues[field.name];
      const placeholder = inferFieldPlaceholder(field);

      if (field.required && isFieldEmpty(field, value)) {
        nextErrors[field.name] = field.requiredMessage || buildRequiredFieldMessage(field, placeholder);
        return;
      }

      const shouldValidateEmail = field.type === 'email' || String(field.name || '').toLowerCase().includes('email');
      if (shouldValidateEmail && !isFieldEmpty(field, value)) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(String(value).trim())) {
          nextErrors[field.name] = FR.common.errors.emailInvalid;
          return;
        }
      }

      if (typeof field.validate === 'function') {
        const customMessage = field.validate(value, formValues);
        if (customMessage) {
          nextErrors[field.name] = customMessage;
        }
      }
    });

    return nextErrors;
  };

  const submitForm = async (event) => {
    event.preventDefault();
    setIsSubmitting(true);
    setError('');
    setSuccessMessage('');

    const validationErrors = validateForm();
    if (Object.keys(validationErrors).length > 0) {
      setFormErrors(validationErrors);
      setError(Object.values(validationErrors)[0]);
      setIsSubmitting(false);
      return;
    }

    const loadingToastId = toast.loading(
      editingItem ? FR.common.feedback.updating : FR.common.feedback.creating
    );

    try {
      const payload = mapFormToPayload ? mapFormToPayload(formValues) : formValues;

      if (editingItem) {
        await service.update(editingItem[idKey], payload);
        setSuccessMessage(FR.common.feedback.updatedSuccess);
      } else {
        await service.create(payload);
        setSuccessMessage(FR.common.feedback.createdSuccess);
      }

      closeModal();
      await loadItems();
    } catch (err) {
      setError(err?.response?.data?.message || err?.message || FR.common.errors.save);
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
      setSuccessMessage(FR.common.feedback.deletedSuccess);
      setIsConfirmOpen(false);
      setDeleteTargetId(null);
      await loadItems();
    } catch (err) {
      setError(err?.response?.data?.message || err?.message || FR.common.errors.delete);
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
            <Button onClick={openCreateModal} variant="primary">
              + {createLabel}
            </Button>
          ) : null
        }
      />

      <div className="surface-card p-4">
        <div
          className="crud-filters-card mb-4 space-y-3"
          role="region"
          aria-label={`Filtres et recherche pour ${title}`}
        >
          <div className="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
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
            <Button variant="secondary" onClick={loadItems} aria-label={`Rafraichir la liste ${title}`}>
              {FR.common.actions.refresh}
            </Button>
          </div>

          {filters.length > 0 ? (
            <div className="grid gap-3 md:grid-cols-2 xl:grid-cols-3">
              {filters.map((filter) => {
                const filterType = filter.type || 'select';

                if (filterType === 'date') {
                  return (
                    <Input
                      key={filter.name}
                      label={filter.label}
                      type="date"
                      value={filterValues[filter.name] ?? ''}
                      onChange={(event) => handleFilterChange(filter.name, event.target.value)}
                    />
                  );
                }

                return (
                  <Select
                    key={filter.name}
                    label={filter.label}
                    value={filterValues[filter.name] ?? ''}
                    onChange={(event) => handleFilterChange(filter.name, event.target.value)}
                    options={filter.options || []}
                  />
                );
              })}
            </div>
          ) : null}
        </div>

        {error ? <Alert variant="danger">{error}</Alert> : null}
        {successMessage ? <Alert variant="success">{successMessage}</Alert> : null}

        {isLoading ? (
          <div className="py-6">
            <Spinner label={FR.common.feedback.loadingData} />
          </div>
        ) : items.length === 0 ? (
          <EmptyState
            icon={emptyState?.icon}
            title={emptyState?.title || FR.common.emptyState.noResultsTitle}
            description={
              emptyState?.description || FR.common.emptyState.noResultsDescription
            }
            actionLabel={canCreate ? emptyState?.actionLabel : undefined}
            onAction={canCreate ? (emptyState?.onAction || openCreateModal) : undefined}
          />
        ) : (
          <div className="overflow-x-auto">
            <table className="crud-table" role="table" aria-label={`Tableau ${title}`}>
              <caption className="sr-only">
                Tableau {title}. Utilisez les filtres et la recherche pour affiner les resultats.
              </caption>
              <thead>
                <tr className="border-b">
                  {columns.map((column) => (
                    <th key={column.key} scope="col">
                      {column.label}
                    </th>
                  ))}
                  {(canEdit || canDelete) ? (
                    <th scope="col" aria-label="Actions disponibles">{FR.common.labels.actions}</th>
                  ) : null}
                </tr>
              </thead>
              <tbody>
                {items.map((item) => {
                  const itemAccessibleName = resolveRowAccessibleName(item, idKey);

                  return (
                    <tr key={item[idKey]} className="border-b">
                      {columns.map((column, columnIndex) => {
                        const cellContent = column.render
                          ? column.render(item)
                          : formatCellValue(item[column.key], column.format);

                        if (columnIndex === 0) {
                          return (
                            <th key={column.key} scope="row" className="crud-table-row-header">
                              {cellContent}
                            </th>
                          );
                        }

                        return <td key={column.key}>{cellContent}</td>;
                      })}

                      {(canEdit || canDelete) ? (
                        <td>
                          <div className="flex gap-2" role="group" aria-label={`Actions pour ${itemAccessibleName}`}>
                            {canEdit ? (
                              <Button
                                variant="ghost"
                                onClick={() => openEditModal(item)}
                                aria-label={`Modifier ${itemAccessibleName}`}
                                title={`Modifier ${itemAccessibleName}`}
                              >
                                {FR.common.actions.edit}
                              </Button>
                            ) : null}
                            {canDelete ? (
                              <Button
                                variant="danger"
                                onClick={() => openDeleteConfirm(item[idKey])}
                                aria-label={`Supprimer ${itemAccessibleName}`}
                                title={`Supprimer ${itemAccessibleName}`}
                              >
                                {FR.common.actions.delete}
                              </Button>
                            ) : null}
                          </div>
                        </td>
                      ) : null}
                    </tr>
                  );
                })}
              </tbody>
            </table>
          </div>
        )}

        <div className="theme-muted mt-4 space-y-3 text-sm">
          <p>{FR.common.labels.total}: {pagination.total}</p>
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
              {FR.common.actions.cancel}
            </Button>
            <Button type="submit" form="crud-form" isLoading={isSubmitting} variant="primary">
              {FR.common.actions.save}
            </Button>
          </div>
        }
      >
        <form id="crud-form" className="grid gap-4 md:grid-cols-2" onSubmit={submitForm}>
          {fields.map((field) => {
            const isHidden = typeof field.hiddenWhen === 'function' && field.hiddenWhen(formValues);
            if (isHidden) {
              return null;
            }

            const fieldError = formErrors[field.name];
            const placeholder = inferFieldPlaceholder(field);
            const helperText = inferFieldHelperText(field);

            if (field.type === 'custom' && typeof field.render === 'function') {
              return (
                <div key={field.name} className={field.fullWidth ? 'md:col-span-2' : ''}>
                  {field.render({
                    value: formValues[field.name],
                    values: formValues,
                    onChange: (nextValue) => handleChange(field.name, nextValue),
                    setValues: (updater) => {
                      setFormValues((prev) => (typeof updater === 'function' ? updater(prev) : updater));
                    },
                    error: fieldError,
                  })}
                  {fieldError ? <span className="ds-field-error">{fieldError}</span> : null}
                  {!fieldError && helperText ? <span className="ds-field-helper">{helperText}</span> : null}
                </div>
              );
            }

            if (field.type === 'select') {
              return (
                <Select
                  key={field.name}
                  label={field.label}
                  value={formValues[field.name] ?? ''}
                  onChange={(event) => handleChange(field.name, event.target.value)}
                  options={field.options || []}
                  required={field.required}
                  error={fieldError}
                  helperText={helperText}
                />
              );
            }

            if (field.type === 'multiselect') {
              return (
                <label key={field.name} className="ds-field-label md:col-span-2">
                  {field.label ? <span className="font-medium">{field.label}</span> : null}
                  <select
                    multiple
                    className="ds-select"
                    value={Array.isArray(formValues[field.name]) ? formValues[field.name] : []}
                    onChange={(event) => {
                      const selectedValues = Array.from(event.target.selectedOptions).map((option) => option.value);
                      handleChange(field.name, selectedValues);
                    }}
                    aria-invalid={Boolean(fieldError)}
                  >
                    {(field.options || []).map((option) => (
                      <option key={option.value} value={option.value}>
                        {option.label}
                      </option>
                    ))}
                  </select>
                  {fieldError ? <span className="ds-field-error">{fieldError}</span> : null}
                  {!fieldError && helperText ? <span className="ds-field-helper">{helperText}</span> : null}
                </label>
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
                    error={fieldError}
                    helperText={helperText}
                    placeholder={placeholder}
                  />
                </div>
              );
            }

            if (field.type === 'checkbox') {
              return (
                <label
                  key={field.name}
                  className="flex items-center gap-2 rounded-xl border px-3 py-2"
                  style={{
                    borderColor: 'var(--color-border)',
                    background: 'var(--color-surface)',
                  }}
                >
                  <input
                    type="checkbox"
                    checked={Boolean(formValues[field.name])}
                    onChange={(event) => handleChange(field.name, event.target.checked)}
                  />
                  <span style={{ fontSize: '13px', color: 'var(--color-text)' }}>{field.label}</span>
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
                error={fieldError}
                helperText={helperText}
                placeholder={placeholder}
              />
            );
          })}
        </form>
      </Modal>

      <ConfirmModal
        isOpen={isConfirmOpen}
        title={FR.common.confirm.deleteTitle}
        message={FR.common.confirm.deleteMessage}
        confirmLabel={FR.common.actions.delete}
        onConfirm={confirmDelete}
        onCancel={closeDeleteConfirm}
        danger
        isConfirming={isDeleting}
      />
    </div>
  );
}
