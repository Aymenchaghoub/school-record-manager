import { useEffect, useMemo, useState } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import apiClient from '../../services/apiClient';
import { createEventsService } from '../../services/eventsService';
import { ROLES } from '../../utils/constants';
import { parseListResponse } from '../../utils/response';

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
  const [classOptions, setClassOptions] = useState([{ value: '', label: 'Toutes les classes' }]);

  const service = useMemo(
    () => createEventsService(role || ROLES.ADMIN),
    [role]
  );

  const canMutate = role === ROLES.ADMIN || role === ROLES.TEACHER;

  useEffect(() => {
    if (role !== ROLES.ADMIN && role !== ROLES.TEACHER) {
      return;
    }

    const endpoint = role === ROLES.ADMIN
      ? '/api/v1/admin/classes'
      : '/api/v1/teacher/classes';

    const loadClasses = async () => {
      try {
        const response = await apiClient.get(endpoint, { params: { per_page: 100 } });
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
          key: 'type',
          label: 'Type',
          render: (item) => <Badge tone="brand">{item.type || '-'}</Badge>,
        },
        {
          key: 'start_date',
          label: 'Debut',
          format: 'datetime',
        },
        { key: 'end_date', label: 'Fin', format: 'datetime' },
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
        { name: 'type', label: 'Type', type: 'select', required: true, options: eventTypeOptions },
        { name: 'start_date', label: 'Debut (date/heure)', type: 'datetime-local', required: true },
        { name: 'end_date', label: 'Fin (date/heure)', type: 'datetime-local' },
        { name: 'location', label: 'Lieu' },
        { name: 'class_id', label: 'Classe cible', type: 'select', options: classOptions },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'target_audience', label: 'Audience cible (JSON array)', type: 'textarea', defaultValue: '[]' },
        { name: 'color', label: 'Couleur', defaultValue: '#3B82F6' },
        { name: 'is_public', label: 'Visible publiquement', type: 'checkbox', defaultValue: true },
        { name: 'is_published', label: 'Publie', type: 'checkbox', defaultValue: true },
      ]}
      mapItemToForm={(item) => ({
        ...item,
        class_id: String(item.class_id || item.class?.id || ''),
        start_date: item.start_date ? String(item.start_date).slice(0, 16) : '',
        end_date: item.end_date ? String(item.end_date).slice(0, 16) : '',
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
          type: values.type || 'other',
          target_audience: targetAudience,
          is_public: Boolean(values.is_public),
          is_published: Boolean(values.is_published),
        };
      }}
    />
  );
}
