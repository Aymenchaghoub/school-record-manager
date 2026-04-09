import { useMemo } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import { createEventsService } from '../../services/eventsService';
import { ROLES } from '../../utils/constants';

const eventTypeOptions = [
  { value: 'exam', label: 'Examen' },
  { value: 'meeting', label: 'Reunion' },
  { value: 'holiday', label: 'Vacances' },
  { value: 'sports', label: 'Sport' },
  { value: 'cultural', label: 'Culturel' },
  { value: 'parent_meeting', label: 'Reunion parents' },
  { value: 'other', label: 'Autre' },
];

export function EventsPage() {
  const { user } = useAuth();
  const role = user?.role;

  const service = useMemo(
    () => createEventsService(role || ROLES.ADMIN),
    [role]
  );

  const canMutate = role === ROLES.ADMIN || role === ROLES.TEACHER;

  return (
    <CrudPage
      title="Evenements"
      description="Calendrier des activites scolaires"
      service={service}
      createLabel="Nouvel evenement"
      canCreate={canMutate}
      canEdit={canMutate}
      canDelete={canMutate}
      columns={[
        { key: 'title', label: 'Titre' },
        {
          key: 'event_type',
          label: 'Type',
          render: (item) => <Badge tone="brand">{item.event_type || item.type || '-'}</Badge>,
        },
        {
          key: 'event_date',
          label: 'Date',
          render: (item) => item.event_date || item.start_date || '-',
        },
        { key: 'location', label: 'Lieu' },
        {
          key: 'is_published',
          label: 'Publie',
          render: (item) => (
            <Badge tone={item.is_published ?? item.is_public ? 'success' : 'warning'}>
              {(item.is_published ?? item.is_public) ? 'Oui' : 'Non'}
            </Badge>
          ),
        },
      ]}
      fields={[
        { name: 'title', label: 'Titre', required: true },
        { name: 'type', label: 'Type principal', type: 'select', required: true, options: eventTypeOptions },
        { name: 'event_type', label: 'Type affiche', type: 'select', options: [{ value: '', label: 'Auto' }, ...eventTypeOptions] },
        { name: 'event_date', label: 'Date evenement', type: 'date' },
        { name: 'event_time', label: 'Heure evenement', type: 'time' },
        { name: 'start_date', label: 'Debut (date/heure)', type: 'datetime-local' },
        { name: 'end_date', label: 'Fin (date/heure)', type: 'datetime-local' },
        { name: 'location', label: 'Lieu' },
        { name: 'class_id', label: 'ID classe cible', type: 'number' },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'target_audience', label: 'Audience cible (JSON array)', type: 'textarea', defaultValue: '[]' },
        { name: 'color', label: 'Couleur', defaultValue: '#3B82F6' },
        { name: 'is_public', label: 'Visible publiquement', type: 'checkbox', defaultValue: true },
        { name: 'is_published', label: 'Publie', type: 'checkbox', defaultValue: true },
      ]}
      mapItemToForm={(item) => ({
        ...item,
        event_type: item.event_type || item.type || '',
        target_audience: Array.isArray(item.target_audience)
          ? JSON.stringify(item.target_audience, null, 2)
          : item.target_audience
            ? JSON.stringify(item.target_audience, null, 2)
            : '[]',
      })}
      mapFormToPayload={(values) => {
        let targetAudience = [];

        try {
          targetAudience = values.target_audience ? JSON.parse(values.target_audience) : [];
        } catch {
          throw new Error('Le champ Audience cible doit etre un JSON valide.');
        }

        return {
          ...values,
          class_id: values.class_id ? Number(values.class_id) : null,
          type: values.type || values.event_type || 'other',
          event_type: values.event_type || values.type || 'other',
          target_audience: targetAudience,
          is_public: Boolean(values.is_public),
          is_published: Boolean(values.is_published),
        };
      }}
    />
  );
}
