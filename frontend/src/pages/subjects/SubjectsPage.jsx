import { useEffect, useMemo, useState } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import FR from '../../i18n/fr';
import apiClient from '../../services/apiClient';
import { createSubjectsService } from '../../services/subjectsService';
import { ROLES } from '../../utils/constants';
import { parseListResponse } from '../../utils/response';

const typeOptions = [
  { value: 'core', label: 'Tronc commun' },
  { value: 'elective', label: 'Option' },
  { value: 'extracurricular', label: 'Parascolaire' },
];

export function SubjectsPage() {
  const { user } = useAuth();
  const role = user?.role;
  const [teacherOptions, setTeacherOptions] = useState([{ value: '', label: 'Selectionner un enseignant' }]);

  const service = useMemo(
    () => createSubjectsService(role || ROLES.ADMIN),
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
      title="Matieres"
      description="Catalogue des matieres et affectation des enseignants"
      service={service}
      createLabel="Nouvelle matiere"
      canCreate={isAdmin}
      canEdit={isAdmin}
      canDelete={isAdmin}
      columns={[
        { key: 'name', label: FR.tables.subjects.name },
        { key: 'code', label: FR.tables.subjects.code },
        {
          key: 'type',
          label: FR.tables.subjects.type,
          render: (item) => <Badge tone="brand">{item.type || '-'}</Badge>,
        },
        { key: 'credits', label: FR.tables.subjects.credits },
        {
          key: 'teacher',
          label: FR.tables.subjects.teacher,
          render: (item) => item.teacher?.name || item.teacher_name || '-',
        },
        {
          key: 'is_active',
          label: FR.tables.subjects.status,
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
        { name: 'type', label: 'Type', type: 'select', required: true, options: typeOptions },
        { name: 'credits', label: 'Credits', type: 'number', defaultValue: 1, required: true },
        { name: 'teacher_id', label: 'Enseignant', type: 'select', options: teacherOptions },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'is_active', label: 'Matiere active', type: 'checkbox', defaultValue: true },
      ]}
      mapItemToForm={(item) => ({
        ...item,
        teacher_id: String(item.teacher_id || item.teacher?.id || ''),
      })}
      mapFormToPayload={(values) => ({
        ...values,
        credits: Number(values.credits || 1),
        teacher_id: values.teacher_id ? Number(values.teacher_id) : null,
        is_active: Boolean(values.is_active),
      })}
    />
  );
}
