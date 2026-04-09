import { useMemo } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import { createAbsencesService } from '../../services/absencesService';
import { ROLES } from '../../utils/constants';

const absenceTypeOptions = [
  { value: 'full_day', label: 'Journee complete' },
  { value: 'partial', label: 'Partielle' },
  { value: 'late_arrival', label: 'Retard' },
  { value: 'early_departure', label: 'Depart anticipe' },
];

const monthFilterOptions = [
  { value: '', label: 'All months' },
  { value: '1', label: 'Jan' },
  { value: '2', label: 'Feb' },
  { value: '3', label: 'Mar' },
  { value: '4', label: 'Apr' },
  { value: '5', label: 'May' },
  { value: '6', label: 'Jun' },
  { value: '7', label: 'Jul' },
  { value: '8', label: 'Aug' },
  { value: '9', label: 'Sep' },
  { value: '10', label: 'Oct' },
  { value: '11', label: 'Nov' },
  { value: '12', label: 'Dec' },
];

export function AbsencesPage() {
  const { user } = useAuth();
  const role = user?.role;
  const currentYear = new Date().getFullYear();

  const service = useMemo(
    () => createAbsencesService(role || ROLES.ADMIN),
    [role]
  );

  const canMutate = role === ROLES.ADMIN || role === ROLES.TEACHER;

  const absenceFilters = useMemo(
    () => [
      {
        name: 'month',
        label: 'Month',
        options: monthFilterOptions,
        defaultValue: '',
      },
    ],
    []
  );

  return (
    <CrudPage
      title="Absences"
      description="Suivi de presence et justification"
      service={service}
      createLabel="Nouvelle absence"
      canCreate={canMutate}
      canEdit={canMutate}
      canDelete={canMutate}
      filters={absenceFilters}
      buildListParams={({ search, page, filters }) => ({
        search,
        page,
        month: filters.month,
        year: filters.month ? currentYear : undefined,
      })}
      columns={[
        {
          key: 'student_name',
          label: 'Eleve',
          render: (item) => item.student?.name || item.student_name || item.student_id || '-',
        },
        {
          key: 'class_name',
          label: 'Classe',
          render: (item) => item.class?.name || item.class_name || item.class_id || '-',
        },
        {
          key: 'type',
          label: 'Type',
          render: (item) => <Badge tone="brand">{item.type || '-'}</Badge>,
        },
        { key: 'absence_date', label: 'Date', format: 'date' },
        {
          key: 'is_justified',
          label: 'Justifiee',
          render: (item) => (
            <Badge tone={item.is_justified ? 'success' : 'warning'}>
              {item.is_justified ? 'Oui' : 'Non'}
            </Badge>
          ),
        },
        { key: 'reason', label: 'Motif' },
      ]}
      fields={[
        { name: 'student_id', label: 'ID eleve', type: 'number', required: true },
        { name: 'class_id', label: 'ID classe', type: 'number', required: true },
        { name: 'subject_id', label: 'ID matiere', type: 'number' },
        { name: 'recorded_by', label: 'ID enseignant', type: 'number' },
        { name: 'absence_date', label: 'Date absence', type: 'date', required: true },
        { name: 'start_time', label: 'Heure debut', type: 'time' },
        { name: 'end_time', label: 'Heure fin', type: 'time' },
        { name: 'type', label: 'Type', type: 'select', required: true, options: absenceTypeOptions },
        { name: 'reason', label: 'Motif' },
        { name: 'justification', label: 'Justification', type: 'textarea' },
        { name: 'is_justified', label: 'Absence justifiee', type: 'checkbox', defaultValue: false },
      ]}
      mapItemToForm={(item) => ({
        ...item,
        student_id: item.student_id || item.student?.id || '',
        class_id: item.class_id || item.class?.id || '',
        subject_id: item.subject_id || item.subject?.id || '',
      })}
      mapFormToPayload={(values) => ({
        ...values,
        student_id: Number(values.student_id),
        class_id: Number(values.class_id),
        subject_id: values.subject_id ? Number(values.subject_id) : null,
        recorded_by: values.recorded_by ? Number(values.recorded_by) : null,
        is_justified: Boolean(values.is_justified),
      })}
      emptyState={{
        title: 'No absences recorded',
        description: 'Track attendance events to keep presence history complete.',
        actionLabel: 'Record an absence',
      }}
    />
  );
}
