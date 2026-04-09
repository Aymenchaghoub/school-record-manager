import { useEffect, useMemo, useState } from 'react';
import { parseEntityResponse, parseListResponse } from '../../utils/response';
import { formatDate, formatDateTime, toBoolean } from '../../utils/format';
import { Alert } from '../ui/Alert';
import { Badge } from '../ui/Badge';
import { Button } from '../ui/Button';
import { Input } from '../ui/Input';
import { Modal } from '../ui/Modal';
import { Select } from '../ui/Select';
import { Spinner } from '../ui/Spinner';
import { Textarea } from '../ui/Textarea';
import { PageHeader } from './PageHeader';

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
}) {
  const [items, setItems] = useState([]);
  const [pagination, setPagination] = useState({ currentPage: 1, totalPages: 1, total: 0 });
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');
  const [search, setSearch] = useState('');
  const [isModalOpen, setIsModalOpen] = useState(false);
  const [editingItem, setEditingItem] = useState(null);
  const [formValues, setFormValues] = useState(buildEmptyValues(fields));
  const [isSubmitting, setIsSubmitting] = useState(false);

  const modalTitle = editingItem ? `Modifier ${title}` : `Creer ${title}`;

  const loadItems = useMemo(
    () => async () => {
      setIsLoading(true);
      setError('');

      try {
        const payload = await service.list({ search, page: pagination.currentPage });
        const parsed = parseListResponse(payload?.data || payload);
        setItems(parsed.items);
        setPagination(parsed.pagination);
      } catch (err) {
        setError(err?.response?.data?.message || err?.message || 'Erreur de chargement');
      } finally {
        setIsLoading(false);
      }
    },
    [service, search, pagination.currentPage]
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

    try {
      const payload = mapFormToPayload ? mapFormToPayload(formValues) : formValues;

      if (editingItem) {
        await service.update(editingItem[idKey], payload);
      } else {
        await service.create(payload);
      }

      closeModal();
      await loadItems();
    } catch (err) {
      setError(err?.response?.data?.message || err?.message || 'Erreur pendant la sauvegarde');
    } finally {
      setIsSubmitting(false);
    }
  };

  const removeItem = async (item) => {
    const confirmed = window.confirm('Confirmer la suppression ?');

    if (!confirmed) {
      return;
    }

    try {
      await service.remove(item[idKey]);
      await loadItems();
    } catch (err) {
      setError(err?.response?.data?.message || err?.message || 'Suppression impossible');
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
          <Input
            placeholder="Recherche..."
            value={search}
            onChange={(event) => {
              setPagination((prev) => ({ ...prev, currentPage: 1 }));
              setSearch(event.target.value);
            }}
            className="md:max-w-sm"
          />
          <Button variant="secondary" onClick={loadItems}>
            Rafraichir
          </Button>
        </div>

        {error ? <Alert variant="danger">{error}</Alert> : null}

        {isLoading ? (
          <div className="py-6">
            <Spinner label="Chargement des donnees..." />
          </div>
        ) : (
          <div className="overflow-x-auto">
            <table className="min-w-full text-left text-sm">
              <thead>
                <tr className="border-b border-slate-200 text-slate-500">
                  {columns.map((column) => (
                    <th key={column.key} className="px-3 py-2 font-semibold">
                      {column.label}
                    </th>
                  ))}
                  {(canEdit || canDelete) ? <th className="px-3 py-2 font-semibold">Actions</th> : null}
                </tr>
              </thead>
              <tbody>
                {items.length === 0 ? (
                  <tr>
                    <td
                      colSpan={columns.length + 1}
                      className="px-3 py-5 text-center text-slate-500"
                    >
                      Aucun resultat
                    </td>
                  </tr>
                ) : (
                  items.map((item) => (
                    <tr key={item[idKey]} className="border-b border-slate-100">
                      {columns.map((column) => (
                        <td key={column.key} className="px-3 py-3 align-top text-slate-700">
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
                              <Button variant="danger" onClick={() => removeItem(item)}>
                                Supprimer
                              </Button>
                            ) : null}
                          </div>
                        </td>
                      ) : null}
                    </tr>
                  ))
                )}
              </tbody>
            </table>
          </div>
        )}

        <div className="mt-4 flex items-center justify-between text-sm text-slate-500">
          <p>Total: {pagination.total}</p>
          <div className="flex items-center gap-2">
            <Button
              variant="secondary"
              disabled={pagination.currentPage <= 1}
              onClick={() => setPagination((prev) => ({ ...prev, currentPage: prev.currentPage - 1 }))}
            >
              Precedent
            </Button>
            <span>
              Page {pagination.currentPage} / {Math.max(1, pagination.totalPages)}
            </span>
            <Button
              variant="secondary"
              disabled={pagination.currentPage >= pagination.totalPages}
              onClick={() => setPagination((prev) => ({ ...prev, currentPage: prev.currentPage + 1 }))}
            >
              Suivant
            </Button>
          </div>
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
    </div>
  );
}
