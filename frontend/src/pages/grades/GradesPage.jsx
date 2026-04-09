import { useMemo } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import { createGradesService } from '../../services/gradesService';
import { ROLES } from '../../utils/constants';

const gradeTypeOptions = [
  { value: 'exam', label: 'Examen' },
  { value: 'quiz', label: 'Quiz' },
  { value: 'assignment', label: 'Devoir' },
  { value: 'project', label: 'Projet' },
  { value: 'participation', label: 'Participation' },
  { value: 'midterm', label: 'Partiel' },
  { value: 'final', label: 'Final' },
];

export function GradesPage() {
  const { user } = useAuth();
  const role = user?.role;

  const service = useMemo(
    () => createGradesService(role || ROLES.ADMIN),
    [role]
  );

  const canMutate = role === ROLES.ADMIN || role === ROLES.TEACHER;

  return (
    <CrudPage
      title="Notes"
      description="Evaluation academique par eleve, matiere et periode"
      service={service}
      createLabel="Nouvelle note"
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
          key: 'subject_name',
          label: 'Matiere',
          render: (item) => item.subject?.name || item.subject_name || item.subject_id || '-',
        },
        {
          key: 'class_name',
          label: 'Classe',
          render: (item) => item.class?.name || item.class_name || item.class_id || '-',
        },
        {
          key: 'value',
          label: 'Note',
          render: (item) => `${item.value ?? '-'} / ${item.max_value ?? '-'}`,
        },
        {
          key: 'type',
          label: 'Type',
          render: (item) => <Badge tone="brand">{item.type || '-'}</Badge>,
        },
        { key: 'grade_date', label: 'Date', format: 'date' },
      ]}
      fields={[
        { name: 'student_id', label: 'ID eleve', type: 'number', required: true },
        { name: 'subject_id', label: 'ID matiere', type: 'number', required: true },
        { name: 'class_id', label: 'ID classe', type: 'number', required: true },
        { name: 'teacher_id', label: 'ID enseignant', type: 'number' },
        { name: 'value', label: 'Valeur', type: 'number', required: true },
        { name: 'max_value', label: 'Valeur max', type: 'number', defaultValue: 100, required: true },
        { name: 'type', label: 'Type', type: 'select', required: true, options: gradeTypeOptions },
        { name: 'title', label: 'Titre' },
        { name: 'grade_date', label: 'Date de note', type: 'date', required: true },
        { name: 'term', label: 'Periode' },
        { name: 'weight', label: 'Coefficient', type: 'number', defaultValue: 1 },
        { name: 'comment', label: 'Commentaire', type: 'textarea' },
      ]}
      mapItemToForm={(item) => ({
        ...item,
        student_id: item.student_id || item.student?.id || '',
        subject_id: item.subject_id || item.subject?.id || '',
        class_id: item.class_id || item.class?.id || '',
        teacher_id: item.teacher_id || item.teacher?.id || '',
      })}
      mapFormToPayload={(values) => ({
        ...values,
        student_id: Number(values.student_id),
        subject_id: Number(values.subject_id),
        class_id: Number(values.class_id),
        teacher_id: values.teacher_id ? Number(values.teacher_id) : null,
        value: Number(values.value),
        max_value: Number(values.max_value || 100),
        weight: Number(values.weight || 1),
      })}
    />
  );
}
