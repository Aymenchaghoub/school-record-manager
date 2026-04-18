import { useEffect, useMemo, useState } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import FR from '../../i18n/fr';
import apiClient from '../../services/apiClient';
import { createClassesService } from '../../services/classesService';
import { ROLES } from '../../utils/constants';
import { parseListResponse } from '../../utils/response';

export function ClassesPage() {
  const { user } = useAuth();
  const role = user?.role;
  const [teacherOptions, setTeacherOptions] = useState([{ value: '', label: 'Selectionner un enseignant' }]);

  const service = useMemo(
    () => createClassesService(role || ROLES.ADMIN),
    [role]
  );

  const isAdmin = role === ROLES.ADMIN;

  useEffect(() => {
    if (!isAdmin) {
      return;
    }

    const loadTeachers = async () => {
      try {
        const response = await apiClient.get('/api/v1/admin/users', {
          params: { role: 'teacher', per_page: 500 },
        });
        const teachers = parseListResponse(response.data?.data || response.data).items;

        setTeacherOptions([
          { value: '', label: 'Selectionner un enseignant' },
          ...teachers.map((teacher) => ({ value: String(teacher.id), label: teacher.name })),
        ]);
      } catch {
        setTeacherOptions([{ value: '', label: 'Selectionner un enseignant' }]);
      }
    };

    loadTeachers();
  }, [isAdmin]);

  return (
    <CrudPage
      title="Classes"
      description="Organisation pedagogique et capacite des classes"
      service={service}
      createLabel="Nouvelle classe"
      canCreate={isAdmin}
      canEdit={isAdmin}
      canDelete={isAdmin}
      columns={[
        { key: 'name', label: FR.tables.classes.name },
        { key: 'code', label: FR.tables.classes.code },
        { key: 'level', label: FR.tables.classes.level },
        { key: 'section', label: FR.tables.classes.section },
        { key: 'academic_year', label: FR.tables.classes.year },
        { key: 'capacity', label: FR.tables.classes.capacity },
        {
          key: 'is_active',
          label: FR.tables.classes.status,
          render: (item) => (
            <Badge tone={item.is_active ? 'success' : 'danger'}>
              {item.is_active ? 'Actif' : 'Inactif'}
            </Badge>
          ),
        },
      ]}
      fields={[
        { name: 'name', label: 'Nom', required: true },
        { name: 'code', label: 'Code', required: true },
        { name: 'level', label: 'Niveau', required: true },
        { name: 'section', label: 'Section' },
        { name: 'academic_year', label: 'Annee academique', required: true },
        { name: 'teacher_id', label: 'Enseignant responsable', type: 'select', options: teacherOptions },
        { name: 'capacity', label: 'Capacite', type: 'number', required: true, defaultValue: 30 },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'is_active', label: 'Classe active', type: 'checkbox', defaultValue: true },
      ]}
      mapItemToForm={(item) => ({
        ...item,
        teacher_id: String(item.teacher_id || item.teacher?.id || ''),
      })}
      mapFormToPayload={(values) => ({
        ...values,
        capacity: Number(values.capacity || 0),
        teacher_id: values.teacher_id
          ? Number(values.teacher_id)
          : null,
        is_active: Boolean(values.is_active),
      })}
      searchPlaceholder="Rechercher une classe par nom..."
      searchDebounceMs={300}
      emptyState={{
        title: 'Aucune classe disponible',
        description: 'Creez une classe pour organiser les eleves et les activites.',
        actionLabel: 'Creer une classe',
      }}
    />
  );
}
