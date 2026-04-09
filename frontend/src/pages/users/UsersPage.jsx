import { useMemo } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import { createUsersService } from '../../services/usersService';
import { ROLES } from '../../utils/constants';
import { formatRole } from '../../utils/format';

const roleOptions = [
  { value: ROLES.ADMIN, label: 'Administrateur' },
  { value: ROLES.TEACHER, label: 'Enseignant' },
  { value: ROLES.STUDENT, label: 'Etudiant' },
  { value: ROLES.PARENT, label: 'Parent' },
];

const genderOptions = [
  { value: '', label: 'Selectionner' },
  { value: 'male', label: 'Masculin' },
  { value: 'female', label: 'Feminin' },
  { value: 'other', label: 'Autre' },
];

export function UsersPage() {
  const { user } = useAuth();

  const service = useMemo(
    () => createUsersService(user?.role || ROLES.ADMIN),
    [user?.role]
  );

  return (
    <CrudPage
      title="Utilisateurs"
      description="Gestion des comptes de la plateforme"
      service={service}
      createLabel="Nouvel utilisateur"
      columns={[
        { key: 'name', label: 'Nom' },
        { key: 'email', label: 'Email' },
        {
          key: 'role',
          label: 'Role',
          render: (item) => <Badge tone="brand">{formatRole(item.role)}</Badge>,
        },
        {
          key: 'is_active',
          label: 'Actif',
          render: (item) => (
            <Badge tone={item.is_active ? 'success' : 'danger'}>
              {item.is_active ? 'Actif' : 'Inactif'}
            </Badge>
          ),
        },
        { key: 'created_at', label: 'Creation', format: 'date' },
      ]}
      fields={[
        { name: 'name', label: 'Nom complet', required: true },
        { name: 'email', label: 'Email', type: 'email', required: true },
        { name: 'password', label: 'Mot de passe', type: 'password' },
        { name: 'role', label: 'Role', type: 'select', required: true, options: roleOptions },
        { name: 'phone', label: 'Telephone' },
        { name: 'date_of_birth', label: 'Date de naissance', type: 'date' },
        { name: 'gender', label: 'Genre', type: 'select', options: genderOptions },
        { name: 'address', label: 'Adresse', type: 'textarea' },
        { name: 'is_active', label: 'Compte actif', type: 'checkbox', defaultValue: true },
      ]}
      mapFormToPayload={(values) => {
        const payload = {
          ...values,
          is_active: Boolean(values.is_active),
        };

        if (!payload.password) {
          delete payload.password;
        }

        if (!payload.gender) {
          payload.gender = null;
        }

        if (!payload.date_of_birth) {
          payload.date_of_birth = null;
        }

        return payload;
      }}
      searchPlaceholder="Search users by name..."
      searchDebounceMs={300}
      emptyState={{
        title: 'No users found',
        description: 'Create your first account to start managing the platform.',
        actionLabel: 'Add your first user',
      }}
    />
  );
}
