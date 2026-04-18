import { useMemo, useState } from 'react';
import toast from 'react-hot-toast';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { Button } from '../../components/ui/Button';
import { useAuth } from '../../hooks/useAuth';
import FR from '../../i18n/fr';
import { createReportCardsService } from '../../services/reportCardsService';
import { ROLES } from '../../utils/constants';
import { parseEntityResponse } from '../../utils/response';

const conductOptions = [
  { value: '', label: 'Selectionner' },
  { value: 'Excellent', label: 'Excellent' },
  { value: 'Very Good', label: 'Tres bien' },
  { value: 'Good', label: 'Bien' },
  { value: 'Fair', label: 'Assez bien' },
  { value: 'Poor', label: 'Insuffisant' },
];

const legacyConductValueMap = {
  'Tres bien': 'Very Good',
  Bien: 'Good',
  'Assez bien': 'Fair',
  Insuffisant: 'Poor',
};

function normalizeSubjectGradeRows(subjectGrades) {
  if (!Array.isArray(subjectGrades)) {
    return [];
  }

  return subjectGrades
    .map((grade) => ({
      subject: String(grade?.subject || grade?.subject_name || '').trim(),
      average: grade?.average ?? '',
    }))
    .filter((grade) => grade.subject || grade.average === 0 || String(grade.average ?? '').trim() !== '');
}

function SubjectGradesBuilder({ value, onChange }) {
  const rows = Array.isArray(value) ? value : [];

  const updateRow = (index, key, nextValue) => {
    const nextRows = rows.map((row, rowIndex) => {
      if (rowIndex !== index) {
        return row;
      }

      return {
        ...row,
        [key]: nextValue,
      };
    });

    onChange(nextRows);
  };

  const addRow = () => {
    onChange([
      ...rows,
      { subject: '', average: '' },
    ]);
  };

  const removeRow = (index) => {
    onChange(rows.filter((_, rowIndex) => rowIndex !== index));
  };

  return (
    <div className="space-y-3 rounded-xl border p-3" style={{ borderColor: 'var(--color-border)' }}>
      <div className="flex items-center justify-between gap-2">
        <span className="text-sm font-medium" style={{ color: 'var(--color-text)' }}>
          Notes par matiere
        </span>
        <Button type="button" variant="secondary" onClick={addRow}>
          + Ajouter
        </Button>
      </div>

      {rows.length === 0 ? (
        <p className="text-xs" style={{ color: 'var(--color-muted)' }}>
          Aucune matiere ajoutee. Cliquez sur "Ajouter" pour creer une ligne.
        </p>
      ) : null}

      {rows.map((row, index) => (
        <div key={`subject-grade-${index}`} className="grid gap-2 md:grid-cols-[1fr_150px_auto] md:items-end">
          <label className="ds-field-label">
            <span className="font-medium">Matiere</span>
            <input
              className="ds-input"
              type="text"
              placeholder="ex: Mathematiques"
              value={row.subject ?? ''}
              onChange={(event) => updateRow(index, 'subject', event.target.value)}
            />
          </label>

          <label className="ds-field-label">
            <span className="font-medium">Moyenne /20</span>
            <input
              className="ds-input"
              type="number"
              step="0.1"
              min="0"
              max="20"
              placeholder="ex: 14.5"
              value={row.average ?? ''}
              onChange={(event) => updateRow(index, 'average', event.target.value)}
            />
          </label>

          <Button
            type="button"
            variant="ghost"
            onClick={() => removeRow(index)}
            aria-label={`Retirer la matiere ${row.subject || index + 1}`}
          >
            Retirer
          </Button>
        </div>
      ))}
    </div>
  );
}

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

const escapeHtml = (value) => String(value ?? '')
  .replace(/&/g, '&amp;')
  .replace(/</g, '&lt;')
  .replace(/>/g, '&gt;')
  .replace(/"/g, '&quot;')
  .replace(/'/g, '&#39;');

const toSafeText = (value, fallback = '-') => {
  if (value === null || value === undefined) {
    return fallback;
  }

  const text = String(value).trim();
  return text === '' ? fallback : text;
};

const toPrintableDate = (value) => {
  if (!value) {
    return '-';
  }

  const parsedDate = new Date(value);
  if (Number.isNaN(parsedDate.getTime())) {
    return toSafeText(value);
  }

  return parsedDate.toLocaleDateString('fr-FR');
};

const normalizePrintableSubjects = (reportCard) => {
  const subjects = Array.isArray(reportCard?.subjects)
    ? reportCard.subjects
    : Array.isArray(reportCard?.subject_grades)
      ? reportCard.subject_grades
      : [];

  return subjects.map((subject, index) => {
    const label = String(subject?.label || '').trim();

    return {
      name: String(subject?.subject || subject?.subject_name || `Matiere ${index + 1}`),
      average: formatSubjectAverageOnTwenty(subject?.average),
      label,
    };
  });
};

const buildPrintableReportCardMarkup = (reportCard) => {
  const subjects = normalizePrintableSubjects(reportCard);
  const studentName = toSafeText(reportCard?.student?.name || reportCard?.student_name);
  const className = toSafeText(reportCard?.class?.name || reportCard?.class_name);
  const term = toSafeText(reportCard?.term);
  const year = toSafeText(reportCard?.academic_year || reportCard?.year);
  const overallAverage = formatAverageOnTwenty(reportCard?.overall_average);
  const totalAbsences = toSafeText(reportCard?.total_absences, '0');
  const justifiedAbsences = toSafeText(reportCard?.justified_absences, '0');
  const rankInClass = toSafeText(reportCard?.rank_in_class);
  const totalStudents = toSafeText(reportCard?.total_students);
  const conductGrade = toSafeText(reportCard?.conduct_grade);
  const issueDate = toPrintableDate(reportCard?.issue_date);
  const isFinal = Boolean(reportCard?.is_final) ? 'Oui' : 'Non';
  const teacherRemarks = toSafeText(reportCard?.teacher_remarks, 'Aucune remarque.');
  const principalRemarks = toSafeText(reportCard?.principal_remarks, 'Aucune remarque.');

  const subjectRows = subjects.length > 0
    ? subjects
      .map(
        (subject) => `
          <tr>
            <td>${escapeHtml(subject.name)}</td>
            <td class="align-right">${escapeHtml(subject.average)}</td>
            <td>${escapeHtml(subject.label || '-')}</td>
          </tr>
        `
      )
      .join('')
    : `
      <tr>
        <td colspan="3" class="empty-row">Aucune matiere disponible.</td>
      </tr>
    `;

  return `
<!doctype html>
<html lang="fr">
  <head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Bulletin - ${escapeHtml(studentName)}</title>
    <style>
      :root {
        color-scheme: light;
      }

      * {
        box-sizing: border-box;
      }

      body {
        margin: 0;
        padding: 24px;
        background: #f5f7fb;
        color: #111827;
        font-family: "Segoe UI", Tahoma, sans-serif;
      }

      .sheet {
        max-width: 960px;
        margin: 0 auto;
        border-radius: 16px;
        border: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 24px;
      }

      .header {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 12px;
        border-bottom: 1px solid #e5e7eb;
        padding-bottom: 14px;
        margin-bottom: 20px;
      }

      .header h1 {
        margin: 0;
        font-size: 26px;
      }

      .header p {
        margin: 4px 0 0;
        color: #6b7280;
      }

      .meta-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
        margin-bottom: 20px;
      }

      .meta-item {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 10px 12px;
        background: #f9fafb;
      }

      .meta-item .label {
        margin: 0;
        font-size: 12px;
        color: #6b7280;
      }

      .meta-item .value {
        margin: 3px 0 0;
        font-size: 15px;
        font-weight: 600;
      }

      table {
        width: 100%;
        border-collapse: collapse;
      }

      thead th {
        text-align: left;
        border-bottom: 1px solid #e5e7eb;
        padding: 10px;
        font-size: 12px;
        color: #6b7280;
        text-transform: uppercase;
        letter-spacing: 0.04em;
      }

      tbody td {
        border-bottom: 1px solid #f3f4f6;
        padding: 10px;
        font-size: 14px;
      }

      .align-right {
        text-align: right;
      }

      .empty-row {
        text-align: center;
        color: #6b7280;
      }

      .remarks {
        margin-top: 18px;
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px;
      }

      .remarks-item {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 12px;
        background: #ffffff;
      }

      .remarks-item h2 {
        margin: 0 0 6px;
        font-size: 14px;
      }

      .remarks-item p {
        margin: 0;
        line-height: 1.5;
      }

      @media print {
        body {
          background: #ffffff;
          padding: 0;
        }

        .sheet {
          border: none;
          border-radius: 0;
          padding: 0;
        }
      }
    </style>
  </head>
  <body>
    <article class="sheet">
      <header class="header">
        <div>
          <h1>Bulletin scolaire</h1>
          <p>Edition individuelle prete a imprimer</p>
        </div>
        <div>
          <strong>Final:</strong> ${escapeHtml(isFinal)}
        </div>
      </header>

      <section class="meta-grid">
        <div class="meta-item">
          <p class="label">Eleve</p>
          <p class="value">${escapeHtml(studentName)}</p>
        </div>
        <div class="meta-item">
          <p class="label">Classe</p>
          <p class="value">${escapeHtml(className)}</p>
        </div>
        <div class="meta-item">
          <p class="label">Periode</p>
          <p class="value">${escapeHtml(term)}</p>
        </div>
        <div class="meta-item">
          <p class="label">Annee academique</p>
          <p class="value">${escapeHtml(year)}</p>
        </div>
        <div class="meta-item">
          <p class="label">Date d emission</p>
          <p class="value">${escapeHtml(issueDate)}</p>
        </div>
        <div class="meta-item">
          <p class="label">Conduite</p>
          <p class="value">${escapeHtml(conductGrade)}</p>
        </div>
        <div class="meta-item">
          <p class="label">Moyenne ponderee (periode)</p>
          <p class="value">${escapeHtml(overallAverage)}</p>
        </div>
        <div class="meta-item">
          <p class="label">Absences</p>
          <p class="value">${escapeHtml(totalAbsences)} total / ${escapeHtml(justifiedAbsences)} justifiees</p>
        </div>
        <div class="meta-item">
          <p class="label">Classement</p>
          <p class="value">${escapeHtml(rankInClass)} sur ${escapeHtml(totalStudents)}</p>
        </div>
      </section>

      <section>
        <table>
          <thead>
            <tr>
              <th>Matiere</th>
              <th class="align-right">Moyenne</th>
              <th>Evaluation</th>
            </tr>
          </thead>
          <tbody>
            ${subjectRows}
          </tbody>
        </table>
      </section>

      <section class="remarks">
        <article class="remarks-item">
          <h2>Remarques enseignant</h2>
          <p>${escapeHtml(teacherRemarks)}</p>
        </article>
        <article class="remarks-item">
          <h2>Remarques direction</h2>
          <p>${escapeHtml(principalRemarks)}</p>
        </article>
      </section>
    </article>
  </body>
</html>
`;
};

export function ReportCardsPage() {
  const { user } = useAuth();
  const role = user?.role;
  const [printingReportCardId, setPrintingReportCardId] = useState(null);

  const service = useMemo(
    () => createReportCardsService(role || ROLES.ADMIN),
    [role]
  );

  const isAdmin = role === ROLES.ADMIN;
  const canPrintSingleReportCard = role === ROLES.ADMIN || role === ROLES.STUDENT;

  const printSingleReportCard = async (item) => {
    const reportCardId = item?.id;

    if (!reportCardId) {
      toast.error('Bulletin introuvable.');
      return;
    }

    setPrintingReportCardId(reportCardId);
    const loadingToastId = toast.loading('Preparation du bulletin...');

    try {
      const payload = await service.get(reportCardId);
      const reportCard = parseEntityResponse(payload);

      if (!reportCard) {
        throw new Error('Bulletin introuvable.');
      }

      const popup = window.open('', '_blank', 'width=980,height=760');
      if (!popup) {
        throw new Error('Autorisez les popups pour imprimer ce bulletin.');
      }

      popup.document.open();
      popup.document.write(buildPrintableReportCardMarkup(reportCard));
      popup.document.close();

      const triggerPrint = () => {
        popup.focus();
        popup.print();
        popup.close();
      };

      if (popup.document.readyState === 'complete') {
        triggerPrint();
      } else {
        popup.onload = triggerPrint;
      }
    } catch (error) {
      toast.error(error?.message || 'Impossible d imprimer ce bulletin.');
    } finally {
      toast.dismiss(loadingToastId);
      setPrintingReportCardId(null);
    }
  };

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
          Imprimer la page
        </button>
      </div>

      <CrudPage
        title="Bulletins"
        description="Synthese des resultats par trimestre et annee. La moyenne affichee est ponderee sur la periode."
        service={service}
        createLabel="Nouveau bulletin"
        canCreate={isAdmin}
        canEdit={isAdmin}
        canDelete={isAdmin}
        columns={[
          {
            key: 'student_name',
            label: FR.tables.reportCards.student,
            render: (item) => item.student?.name || item.student_name || item.student_id || '-',
          },
          {
            key: 'class_name',
            label: FR.tables.reportCards.class,
            render: (item) => item.class?.name || item.class_name || item.class_id || '-',
          },
          { key: 'term', label: FR.tables.reportCards.term },
          { key: 'academic_year', label: FR.tables.reportCards.year },
          {
            key: 'overall_average',
            label: 'Moyenne ponderee (periode)',
            render: (item) => (
              <span className="font-semibold">{formatAverageOnTwenty(item.overall_average)}</span>
            ),
          },
          {
            key: 'subjects',
            label: FR.tables.reportCards.perSubjectAverages,
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
          { key: 'total_absences', label: FR.tables.reportCards.absences },
          {
            key: 'is_final',
            label: FR.tables.reportCards.final,
            render: (item) => (
              <Badge tone={item.is_final ? 'success' : 'slate'}>
                {item.is_final ? 'Oui' : 'Non'}
              </Badge>
            ),
          },
          ...(canPrintSingleReportCard
            ? [
              {
                key: 'single_print',
                label: 'Bulletin',
                render: (item) => {
                  const studentName = item.student?.name || item.student_name || item.student_id || 'eleve';
                  const isPrinting = printingReportCardId === item.id;

                  return (
                    <Button
                      variant="secondary"
                      onClick={() => printSingleReportCard(item)}
                      isLoading={isPrinting}
                      aria-label={`Voir et imprimer le bulletin de ${studentName}`}
                      title={`Voir et imprimer le bulletin de ${studentName}`}
                    >
                      Voir / Imprimer
                    </Button>
                  );
                },
              },
            ]
            : []),
        ]}
        fields={[
          {
            name: 'student_id',
            label: 'ID eleve',
            type: 'number',
            required: true,
            helperText: 'Utilisez l identifiant de l eleve pour garantir le bon rattachement.',
          },
          {
            name: 'class_id',
            label: 'ID classe',
            type: 'number',
            required: true,
            helperText: 'Doit correspondre a la classe suivie par l eleve.',
          },
          {
            name: 'term',
            label: 'Periode',
            required: true,
            placeholder: 'ex: Trimestre 2',
          },
          { name: 'academic_year', label: 'Annee academique', required: true, placeholder: 'ex: 2025-2026' },
          { name: 'overall_average', label: 'Moyenne generale', type: 'number', helperText: 'Optionnel: laisse vide pour calcul automatique.' },
          { name: 'total_absences', label: 'Total des absences', type: 'number', defaultValue: 0 },
          { name: 'justified_absences', label: 'Absences justifiees', type: 'number', defaultValue: 0 },
          { name: 'rank_in_class', label: 'Rang', type: 'number' },
          { name: 'total_students', label: 'Total eleves', type: 'number' },
          { name: 'conduct_grade', label: 'Conduite', type: 'select', options: conductOptions },
          { name: 'issue_date', label: 'Date emission', type: 'date', required: true },
          {
            name: 'subject_grades_builder',
            label: 'Notes par matiere',
            type: 'custom',
            fullWidth: true,
            helperText: 'Ajoutez les matieres et leurs moyennes sur 20.',
            validate: (value) => {
              const rows = Array.isArray(value) ? value : [];
              const nonEmptyRows = rows.filter((row) => {
                const hasSubject = String(row?.subject || '').trim() !== '';
                const hasAverage = String(row?.average ?? '').trim() !== '';
                return hasSubject || hasAverage;
              });

              if (nonEmptyRows.length === 0) {
                return 'Ajoutez au moins une matiere avec sa moyenne.';
              }

              for (let index = 0; index < nonEmptyRows.length; index += 1) {
                const row = nonEmptyRows[index];
                const subject = String(row?.subject || '').trim();

                if (!subject) {
                  return `Renseignez le nom de la matiere pour la ligne ${index + 1}.`;
                }

                const average = Number(row?.average);
                if (!Number.isFinite(average)) {
                  return `Renseignez une moyenne numerique valide pour ${subject}.`;
                }

                if (average < 0 || average > 20) {
                  return `La moyenne de ${subject} doit etre comprise entre 0 et 20.`;
                }
              }

              return '';
            },
            render: ({ value, onChange }) => (
              <SubjectGradesBuilder value={value} onChange={onChange} />
            ),
          },
          { name: 'teacher_remarks', label: 'Remarques enseignant', type: 'textarea' },
          { name: 'principal_remarks', label: 'Remarques direction', type: 'textarea' },
          { name: 'is_final', label: 'Version finale', type: 'checkbox', defaultValue: false },
        ]}
        mapItemToForm={(item) => ({
          ...item,
          student_id: item.student_id || item.student?.id || '',
          class_id: item.class_id || item.class?.id || '',
        subject_grades_builder: normalizeSubjectGradeRows(
          Array.isArray(item.subjects)
            ? item.subjects
            : Array.isArray(item.subject_grades)
              ? item.subject_grades
              : []
        ),
        conduct_grade: legacyConductValueMap[item.conduct_grade] || item.conduct_grade || '',
        })}
        mapFormToPayload={(values) => {
          const subjectGrades = normalizeSubjectGradeRows(values.subject_grades_builder)
            .map((grade) => ({
              subject: String(grade.subject || '').trim(),
              average: Number(grade.average),
            }))
            .filter((grade) => grade.subject !== '');

          const payload = {
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

          delete payload.subject_grades_builder;

          return payload;
        }}
      />
    </div>
  );
}
