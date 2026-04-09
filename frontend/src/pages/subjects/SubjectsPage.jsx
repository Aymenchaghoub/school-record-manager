import { useMemo } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import { createSubjectsService } from '../../services/subjectsService';
import { ROLES } from '../../utils/constants';

const typeOptions = [
  { value: 'core', label: 'Tronc commun' },
  { value: 'elective', label: 'Option' },
  { value: 'extracurricular', label: 'Parascolaire' },
];

export function SubjectsPage() {
  const { user } = useAuth();
  const role = user?.role;

  const service = useMemo(
    () => createSubjectsService(role || ROLES.ADMIN),
    [role]
  );

  const isAdmin = role === ROLES.ADMIN;

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
        { key: 'name', label: 'Nom' },
        { key: 'code', label: 'Code' },
        {
          key: 'type',
          label: 'Type',
          render: (item) => <Badge tone="brand">{item.type || '-'}</Badge>,
        },
        { key: 'credits', label: 'Credits' },
        { key: 'teacher_id', label: 'ID enseignant' },
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
        { name: 'type', label: 'Type', type: 'select', required: true, options: typeOptions },
        { name: 'credits', label: 'Credits', type: 'number', defaultValue: 1, required: true },
        { name: 'teacher_id', label: 'ID enseignant', type: 'number' },
        { name: 'description', label: 'Description', type: 'textarea' },
        { name: 'is_active', label: 'Matiere active', type: 'checkbox', defaultValue: true },
      ]}
      mapFormToPayload={(values) => ({
        ...values,
        credits: Number(values.credits || 1),
        teacher_id: values.teacher_id ? Number(values.teacher_id) : null,
        is_active: Boolean(values.is_active),
      })}
    />
  );
}
