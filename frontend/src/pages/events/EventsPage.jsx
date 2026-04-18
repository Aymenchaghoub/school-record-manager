import { useCallback, useEffect, useMemo, useState } from 'react';
import CalendarView from '../../components/common/CalendarView';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { useAuth } from '../../hooks/useAuth';
import FR from '../../i18n/fr';
import apiClient from '../../services/apiClient';
import { createEventsService } from '../../services/eventsService';
import { ROLES } from '../../utils/constants';
import { parseListResponse } from '../../utils/response';

const eventTypeOptions = [
  { value: '', label: 'Selectionner un type' },
  { value: 'exam', label: 'Examen' },
  { value: 'meeting', label: 'Reunion' },
  { value: 'holiday', label: 'Vacances' },
  { value: 'sports', label: 'Sport' },
  { value: 'cultural', label: 'Culturel' },
  { value: 'parent_meeting', label: 'Reunion parents' },
  { value: 'other', label: 'Autre' },
];

const audienceScopeOptions = [
  { value: 'all_classes', label: 'Toutes les classes' },
  { value: 'selected_classes', label: 'Classes specifiques' },
];

function normalizeAudienceSelection(targetAudience) {
  const source = Array.isArray(targetAudience) ? targetAudience : [];
  const normalized = source.map((entry) => String(entry).trim()).filter(Boolean);
  const hasAllClasses = normalized.some((entry) => {
    const lowerEntry = entry.toLowerCase();
    return lowerEntry === 'all' || lowerEntry === 'all_classes';
  });

  const classIds = normalized
    .filter((entry) => {
      const lowerEntry = entry.toLowerCase();
      return lowerEntry !== 'all' && lowerEntry !== 'all_classes';
    })
    .map((entry) => (entry.startsWith('class:') ? entry.slice(6) : entry))
    .filter(Boolean);

  return {
    scope: hasAllClasses || classIds.length === 0 ? 'all_classes' : 'selected_classes',
    classIds,
  };
}

function resolveEventColor(type) {
  if (type === 'exam') {
    return '#EF4444';
  }

  if (type === 'holiday') {
    return '#22C55E';
  }

  return '#A855F7';
}

function formatDateTime(value) {
  if (!value) {
    return '-';
  }

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) {
    return String(value);
  }

  return parsed.toLocaleString('fr-FR');
}

export function EventsPage() {
  const { user } = useAuth();
  const role = user?.role;
  const [classOptions, setClassOptions] = useState([{ value: '', label: 'Toutes les classes' }]);
  const [viewMode, setViewMode] = useState('table');
  const [calendarItems, setCalendarItems] = useState([]);
  const [isCalendarLoading, setIsCalendarLoading] = useState(false);
  const [calendarError, setCalendarError] = useState('');
  const [selectedCalendarEvent, setSelectedCalendarEvent] = useState(null);

  const audienceClassOptions = useMemo(
    () => classOptions.filter((option) => option.value !== ''),
    [classOptions]
  );

  const service = useMemo(
    () => createEventsService(role || ROLES.ADMIN),
    [role]
  );

  const canMutate = role === ROLES.ADMIN || role === ROLES.TEACHER;

  const loadCalendarItems = useCallback(async () => {
    setIsCalendarLoading(true);
    setCalendarError('');

    try {
      const payload = await service.list({ page: 1, per_page: 500 });
      const items = parseListResponse(payload?.data || payload).items;
      setCalendarItems(items);
    } catch (error) {
      setCalendarError(error?.message || 'Impossible de charger les evenements pour le calendrier.');
    } finally {
      setIsCalendarLoading(false);
    }
  }, [service]);

  useEffect(() => {
    if (viewMode !== 'calendar') {
      return;
    }

    loadCalendarItems();
  }, [viewMode, loadCalendarItems]);

  const calendarEvents = useMemo(
    () => calendarItems.map((event) => ({
      id: event.id,
      title: event.title,
      start: event.start_date,
      end: event.end_date,
      color: resolveEventColor(event.type),
      extendedProps: {
        description: event.description,
        location: event.location,
        type: event.type,
      },
    })),
    [calendarItems]
  );

  const handleCalendarEventClick = useCallback((clickInfo) => {
    const { event } = clickInfo;

    setSelectedCalendarEvent({
      id: event.id,
      title: event.title,
      start: event.start,
      end: event.end,
      ...event.extendedProps,
    });
  }, []);

  useEffect(() => {
    if (role !== ROLES.ADMIN && role !== ROLES.TEACHER) {
      return;
    }

    const endpoint = role === ROLES.ADMIN
      ? '/api/v1/admin/classes'
      : '/api/v1/teacher/classes';

    const loadClasses = async () => {
      try {
        const response = await apiClient.get(endpoint, { params: { per_page: 500 } });
        const classes = parseListResponse(response.data?.data || response.data).items;

        setClassOptions([
          { value: '', label: 'Toutes les classes' },
          ...classes.map((classItem) => ({ value: String(classItem.id), label: classItem.name })),
        ]);
      } catch {
        setClassOptions([{ value: '', label: 'Toutes les classes' }]);
      }
    };

    loadClasses();
  }, [role]);

  return (
    <div className="space-y-4">
      <div className="surface-card flex flex-wrap items-center justify-between gap-3 p-4">
        <div className="flex items-center gap-2">
          <Button
            variant={viewMode === 'table' ? 'primary' : 'secondary'}
            onClick={() => setViewMode('table')}
          >
            Tableau
          </Button>
          <Button
            variant={viewMode === 'calendar' ? 'primary' : 'secondary'}
            onClick={() => setViewMode('calendar')}
          >
            Calendrier
          </Button>
        </div>

        {viewMode === 'calendar' ? (
          <Button variant="secondary" onClick={loadCalendarItems}>
            Rafraichir
          </Button>
        ) : null}
      </div>

      {viewMode === 'table' ? (
        <CrudPage
          title="Evenements"
          description="Calendrier des activites scolaires"
          service={service}
          createLabel="Nouvel evenement"
          canCreate={canMutate}
          canEdit={canMutate}
          canDelete={canMutate}
          columns={[
            { key: 'title', label: FR.tables.events.title },
            {
              key: 'type',
              label: FR.tables.events.type,
              render: (item) => <Badge tone="brand">{item.type || '-'}</Badge>,
            },
            {
              key: 'start_date',
              label: FR.tables.events.start,
              format: 'datetime',
            },
            { key: 'end_date', label: FR.tables.events.end, format: 'datetime' },
            { key: 'location', label: FR.tables.events.location },
            {
              key: 'is_published',
              label: FR.tables.events.published,
              render: (item) => (
                <Badge tone={item.is_published ?? item.is_public ? 'success' : 'warning'}>
                  {(item.is_published ?? item.is_public) ? 'Oui' : 'Non'}
                </Badge>
              ),
            },
          ]}
          fields={[
            {
              name: 'title',
              label: 'Titre',
              required: true,
              helperText: 'Soyez precis pour faciliter la recherche rapide dans le calendrier.',
            },
            {
              name: 'type',
              label: 'Type',
              type: 'select',
              required: true,
              options: eventTypeOptions,
              helperText: 'Choisissez la categorie qui correspond le mieux a l evenement.',
            },
            { name: 'start_date', label: 'Debut (date/heure)', type: 'datetime-local', required: true },
            { name: 'end_date', label: 'Fin (date/heure)', type: 'datetime-local' },
            { name: 'location', label: 'Lieu' },
            {
              name: 'class_id',
              label: 'Classe cible (optionnelle)',
              type: 'select',
              options: classOptions,
              helperText: 'Laissez vide pour un evenement general.',
            },
            {
              name: 'audience_scope',
              label: 'Audience cible',
              type: 'select',
              options: audienceScopeOptions,
              defaultValue: 'all_classes',
              helperText: 'Utilisez la selection specifique si seules certaines classes sont concernees.',
            },
            {
              name: 'audience_class_ids',
              label: 'Classes concernees',
              type: 'multiselect',
              options: audienceClassOptions,
              hiddenWhen: (values) => values.audience_scope !== 'selected_classes',
              helperText: 'Maintenez Ctrl (Windows) ou Cmd (Mac) pour selectionner plusieurs classes.',
              requiredMessage: 'Selectionnez au moins une classe ciblee.',
              validate: (value, values) => {
                if (values.audience_scope !== 'selected_classes') {
                  return '';
                }

                if (!Array.isArray(value) || value.length === 0) {
                  return 'Selectionnez au moins une classe ciblee.';
                }

                return '';
              },
            },
            { name: 'description', label: 'Description', type: 'textarea' },
            { name: 'color', label: 'Couleur', defaultValue: '#3B82F6' },
            { name: 'is_public', label: 'Visible publiquement', type: 'checkbox', defaultValue: true },
            { name: 'is_published', label: 'Publie', type: 'checkbox', defaultValue: true },
          ]}
          mapItemToForm={(item) => {
            const audienceSelection = normalizeAudienceSelection(item.target_audience);

            return {
              ...item,
              class_id: String(item.class_id || item.class?.id || ''),
              start_date: item.start_date ? String(item.start_date).slice(0, 16) : '',
              end_date: item.end_date ? String(item.end_date).slice(0, 16) : '',
              audience_scope: audienceSelection.scope,
              audience_class_ids: audienceSelection.classIds,
            };
          }}
          mapFormToPayload={(values) => {
            const targetAudience = values.audience_scope === 'selected_classes'
              ? (Array.isArray(values.audience_class_ids) ? values.audience_class_ids.map(String).filter(Boolean) : [])
              : ['all_classes'];

            const payload = {
              ...values,
              class_id: values.class_id ? Number(values.class_id) : null,
              type: values.type || 'other',
              target_audience: targetAudience,
              is_public: Boolean(values.is_public),
              is_published: Boolean(values.is_published),
            };

            delete payload.audience_scope;
            delete payload.audience_class_ids;

            return payload;
          }}
        />
      ) : (
        <>
          {calendarError ? (
            <div className="surface-card p-4" style={{ color: '#dc2626' }}>
              {calendarError}
            </div>
          ) : null}

          {isCalendarLoading ? (
            <div className="surface-card p-6" style={{ color: 'var(--color-muted)', fontSize: '14px' }}>
              Chargement du calendrier...
            </div>
          ) : (
            <CalendarView events={calendarEvents} onEventClick={handleCalendarEventClick} />
          )}

          <Modal
            title={selectedCalendarEvent?.title || 'Evenement'}
            isOpen={Boolean(selectedCalendarEvent)}
            onClose={() => setSelectedCalendarEvent(null)}
            footer={
              <div className="flex justify-end">
                <Button variant="secondary" onClick={() => setSelectedCalendarEvent(null)}>
                  Fermer
                </Button>
              </div>
            }
          >
            <div className="space-y-2 text-sm" style={{ color: 'var(--color-text)' }}>
              <p><strong>Type:</strong> {selectedCalendarEvent?.type || '-'}</p>
              <p><strong>Debut:</strong> {formatDateTime(selectedCalendarEvent?.start)}</p>
              <p><strong>Fin:</strong> {formatDateTime(selectedCalendarEvent?.end)}</p>
              <p><strong>Lieu:</strong> {selectedCalendarEvent?.location || '-'}</p>
              <p><strong>Description:</strong> {selectedCalendarEvent?.description || '-'}</p>
            </div>
          </Modal>
        </>
      )}
    </div>
  );
}
