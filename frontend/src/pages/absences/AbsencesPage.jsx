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

export function AbsencesPage() {
  const { user } = useAuth();
  const role = user?.role;

  const service = useMemo(
    () => createAbsencesService(role || ROLES.ADMIN),
    [role]
  );

  const canMutate = role === ROLES.ADMIN || role === ROLES.TEACHER;

  return (
    <CrudPage
      title="Absences"
      description="Suivi de presence et justification"
      service={service}
      createLabel="Nouvelle absence"
      canCreate={canMutate}
      canEdit={canMutate}
      canDelete={canMutate}
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
    />
  );
}
