import { useMemo } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import { createReportCardsService } from '../../services/reportCardsService';
import { ROLES } from '../../utils/constants';

const conductOptions = [
  { value: '', label: 'Selectionner' },
  { value: 'Excellent', label: 'Excellent' },
  { value: 'Tres bien', label: 'Tres bien' },
  { value: 'Bien', label: 'Bien' },
  { value: 'Assez bien', label: 'Assez bien' },
  { value: 'Insuffisant', label: 'Insuffisant' },
];

const formatAverageOnTwenty = (average) => {
  if (average === null || average === undefined || average === '') {
    return '—';
  }

  const parsedAverage = Number(average);
  if (!Number.isFinite(parsedAverage)) {
    return '—';
  }

  return `${parsedAverage.toFixed(2)}/20`;
};

const formatSubjectAverageOnTwenty = (average) => {
  if (average === null || average === undefined || average === '') {
    return '—';
  }

  const parsedAverage = Number(average);
  if (!Number.isFinite(parsedAverage)) {
    return '—';
  }

  return `${parsedAverage.toFixed(1)}/20`;
};

const performanceBadge = (average) => {
  if (average === null || average === undefined || average === '') return null;

  const parsedAverage = Number(average);
  if (!Number.isFinite(parsedAverage)) return null;

  if (parsedAverage >= 16) return <Badge tone="success">Excellent</Badge>;
  if (parsedAverage >= 12) return <Badge tone="info">Bien</Badge>;
  if (parsedAverage >= 10) return <Badge tone="warning">Passable</Badge>;
  return <Badge tone="danger">Insuffisant</Badge>;
};

export function ReportCardsPage() {
  const { user } = useAuth();
  const role = user?.role;

  const service = useMemo(
    () => createReportCardsService(role || ROLES.ADMIN),
    [role]
  );

  const isAdmin = role === ROLES.ADMIN;

  return (
    <div className="report-card-content space-y-4">
      <div
        className="report-card-header flex items-center justify-between gap-3 rounded-xl border p-4"
        style={{ borderColor: 'var(--color-border)', background: 'var(--color-surface)' }}
      >
        <div>
          <h2 className="text-lg font-semibold" style={{ color: 'var(--color-text)' }}>Bulletin scolaire</h2>
          <p className="text-sm" style={{ color: 'var(--color-muted)' }}>Impression propre pour consultation et archivage</p>
        </div>
        <button
          onClick={() => window.print()}
          className="print:hidden flex items-center gap-2 rounded-lg border px-4 py-2 text-sm font-medium transition-colors"
          style={{
            borderColor: 'var(--color-border)',
            color: 'var(--color-text)',
            background: 'var(--color-surface)',
          }}
          aria-label="Imprimer ce bulletin"
        >
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round" aria-hidden="true">
            <polyline points="6 9 6 2 18 2 18 9" />
            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2" />
            <rect x="6" y="14" width="12" height="8" />
          </svg>
          Imprimer
        </button>
      </div>

      <CrudPage
        title="Bulletins"
        description="Synthese des resultats par trimestre et annee"
        service={service}
        createLabel="Nouveau bulletin"
        canCreate={isAdmin}
        canEdit={isAdmin}
        canDelete={isAdmin}
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
          { key: 'term', label: 'Periode' },
          { key: 'academic_year', label: 'Annee' },
          {
            key: 'overall_average',
            label: 'Moyenne',
            render: (item) => (
              <span className="font-semibold">{formatAverageOnTwenty(item.overall_average)}</span>
            ),
          },
          {
            key: 'subjects',
            label: 'Moyennes par matiere',
            render: (item) => {
              const subjects = Array.isArray(item.subjects)
                ? item.subjects
                : Array.isArray(item.subject_grades)
                  ? item.subject_grades
                  : [];

              if (subjects.length === 0) {
                return '—';
              }

              return (
                <div className="space-y-1.5">
                  {subjects.map((subject, index) => (
                    <div key={`${subject.subject || subject.subject_name || 'subject'}-${index}`} className="flex items-center justify-between gap-2">
                      <span className="text-xs" style={{ color: 'var(--color-muted)' }}>
                        {subject.subject || subject.subject_name || `Matiere ${index + 1}`}
                      </span>
                      <div className="flex items-center gap-2">
                        <span className="font-semibold">
                          {formatSubjectAverageOnTwenty(subject.average)}
                        </span>
                        {performanceBadge(subject.average)}
                      </div>
                    </div>
                  ))}
                </div>
              );
            },
          },
          { key: 'total_absences', label: 'Absences' },
          {
            key: 'is_final',
            label: 'Final',
            render: (item) => (
              <Badge tone={item.is_final ? 'success' : 'slate'}>
                {item.is_final ? 'Oui' : 'Non'}
              </Badge>
            ),
          },
        ]}
        fields={[
          { name: 'student_id', label: 'ID eleve', type: 'number', required: true },
          { name: 'class_id', label: 'ID classe', type: 'number', required: true },
          { name: 'term', label: 'Periode', required: true },
          { name: 'academic_year', label: 'Annee academique', required: true },
          { name: 'overall_average', label: 'Moyenne generale', type: 'number' },
          { name: 'total_absences', label: 'Total des absences', type: 'number', defaultValue: 0 },
          { name: 'justified_absences', label: 'Absences justifiees', type: 'number', defaultValue: 0 },
          { name: 'rank_in_class', label: 'Rang', type: 'number' },
          { name: 'total_students', label: 'Total eleves', type: 'number' },
          { name: 'conduct_grade', label: 'Conduite', type: 'select', options: conductOptions },
          { name: 'issue_date', label: 'Date emission', type: 'date', required: true },
          { name: 'subject_grades', label: 'JSON notes par matiere', type: 'textarea', required: true, defaultValue: '[]' },
          { name: 'teacher_remarks', label: 'Remarques enseignant', type: 'textarea' },
          { name: 'principal_remarks', label: 'Remarques direction', type: 'textarea' },
          { name: 'is_final', label: 'Version finale', type: 'checkbox', defaultValue: false },
        ]}
        mapItemToForm={(item) => ({
          ...item,
          student_id: item.student_id || item.student?.id || '',
          class_id: item.class_id || item.class?.id || '',
          subject_grades: Array.isArray(item.subjects)
            ? JSON.stringify(item.subjects, null, 2)
            : Array.isArray(item.subject_grades)
              ? JSON.stringify(item.subject_grades, null, 2)
              : item.subject_grades
                ? JSON.stringify(item.subject_grades, null, 2)
                : '[]',
        })}
        mapFormToPayload={(values) => {
          let subjectGrades = [];

          try {
            subjectGrades = values.subject_grades ? JSON.parse(values.subject_grades) : [];
          } catch {
            throw new Error('Le champ JSON notes par matiere est invalide.');
          }

          return {
            ...values,
            student_id: Number(values.student_id),
            class_id: Number(values.class_id),
            overall_average: values.overall_average ? Number(values.overall_average) : null,
            total_absences: Number(values.total_absences || 0),
            justified_absences: Number(values.justified_absences || 0),
            rank_in_class: values.rank_in_class ? Number(values.rank_in_class) : null,
            total_students: values.total_students ? Number(values.total_students) : null,
            conduct_grade: values.conduct_grade || null,
            subject_grades: subjectGrades,
            is_final: Boolean(values.is_final),
          };
        }}
      />
    </div>
  );
}
