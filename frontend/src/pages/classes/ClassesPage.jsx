import { useMemo } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import { createClassesService } from '../../services/classesService';
import { ROLES } from '../../utils/constants';

export function ClassesPage() {
  const { user } = useAuth();
  const role = user?.role;

  const service = useMemo(
    () => createClassesService(role || ROLES.ADMIN),
    [role]
  );

  const isAdmin = role === ROLES.ADMIN;

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
        { key: 'name', label: 'Nom' },
        { key: 'code', label: 'Code' },
        { key: 'level', label: 'Niveau' },
        { key: 'section', label: 'Section' },
        { key: 'academic_year', label: 'Annee' },
        { key: 'capacity', label: 'Capacite' },
        {
          key: 'is_active',
          label: 'Etat',
          render: (item) => (
            <Badge tone={item.is_active ? 'success' : 'danger'}>
              {item.is_active ? 'Active' : 'Inactive'}
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
        { name: 'responsible_teacher_id', label: 'ID enseignant responsable', type: 'number' },
        { name: 'capacity', label: 'Capacite', type: 'number', required: true, defaultValue: 30 },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'is_active', label: 'Classe active', type: 'checkbox', defaultValue: true },
      ]}
      mapFormToPayload={(values) => ({
        ...values,
        capacity: Number(values.capacity || 0),
        responsible_teacher_id: values.responsible_teacher_id
          ? Number(values.responsible_teacher_id)
          : null,
        is_active: Boolean(values.is_active),
      })}
      searchPlaceholder="Search classes by name..."
      searchDebounceMs={300}
      emptyState={{
        title: 'No classes yet',
        description: 'Create a class to organize students and schedule activities.',
        actionLabel: 'Create a class',
      }}
    />
  );
}
