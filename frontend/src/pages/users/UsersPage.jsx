import { useCallback, useMemo } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import FR from '../../i18n/fr';
import { createUsersService } from '../../services/usersService';
import { ROLES } from '../../utils/constants';
import { formatRole } from '../../utils/format';

const roleOptions = [
  { value: '', label: 'Selectionner un role' },
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

  const userFilters = useMemo(
    () => [
      {
        name: 'role_filter',
        label: 'Role',
        defaultValue: '',
        options: [
          { value: '', label: 'Tous les roles' },
          ...roleOptions.filter((option) => option.value),
        ],
      },
    ],
    []
  );

  const applyClientFilters = useCallback((loadedItems, activeFilters) => {
    if (!activeFilters.role_filter) {
      return loadedItems;
    }

    return loadedItems.filter((item) => String(item.role || '') === activeFilters.role_filter);
  }, []);

  const buildUsersListParams = useCallback(
    ({ search, page }) => ({
      search,
      page,
      per_page: 500,
    }),
    []
  );

  return (
    <CrudPage
      title="Utilisateurs"
      description="Gestion des comptes de la plateforme"
      service={service}
      createLabel="Nouvel utilisateur"
      filters={userFilters}
      includeFiltersInRequest={false}
      applyClientFilters={applyClientFilters}
      buildListParams={buildUsersListParams}
      columns={[
        { key: 'name', label: FR.tables.users.name },
        { key: 'email', label: FR.tables.users.email },
        {
          key: 'role',
          label: FR.tables.users.role,
          render: (item) => <Badge tone="brand">{formatRole(item.role)}</Badge>,
        },
        {
          key: 'is_active',
          label: FR.tables.users.active,
          render: (item) => (
            <Badge tone={item.is_active ? 'success' : 'danger'}>
              {item.is_active ? 'Actif' : 'Inactif'}
            </Badge>
          ),
        },
        { key: 'created_at', label: FR.tables.users.createdAt, format: 'date' },
      ]}
      fields={[
        {
          name: 'name',
          label: 'Nom complet',
          required: true,
          helperText: 'Entrez le nom et le prenom pour faciliter la recherche.',
        },
        {
          name: 'email',
          label: 'Email',
          type: 'email',
          required: true,
          helperText: 'Utilisez une adresse institutionnelle si possible.',
        },
        {
          name: 'password',
          label: 'Mot de passe',
          type: 'password',
          helperText: 'Laissez vide pour conserver le mot de passe actuel lors d une modification.',
          validate: (value) => {
            if (!value) {
              return '';
            }

            if (String(value).length < 8) {
              return 'Le mot de passe doit contenir au moins 8 caracteres.';
            }

            return '';
          },
        },
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
      searchPlaceholder="Rechercher un utilisateur par nom..."
      searchDebounceMs={300}
      emptyState={{
        title: 'Aucun utilisateur trouve',
        description: 'Creez un premier compte pour demarrer la gestion de la plateforme.',
        actionLabel: 'Ajouter un utilisateur',
      }}
    />
  );
}
