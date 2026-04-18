import { useCallback, useEffect, useMemo, useState } from 'react';
import CalendarView from '../../components/common/CalendarView';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { Button } from '../../components/ui/Button';
import { Modal } from '../../components/ui/Modal';
import { useAuth } from '../../hooks/useAuth';
import FR from '../../i18n/fr';
import apiClient from '../../services/apiClient';
import { createAbsencesService } from '../../services/absencesService';
import { ROLES } from '../../utils/constants';
import { parseListResponse } from '../../utils/response';

const absenceTypeOptions = [
  { value: 'full_day', label: 'Journee complete' },
  { value: 'partial', label: 'Partielle' },
  { value: 'late_arrival', label: 'Retard' },
  { value: 'early_departure', label: 'Depart anticipe' },
];

const absenceStatusFilterOptions = [
  { value: '', label: 'Tous les statuts' },
  { value: 'justified', label: 'Justifiee' },
  { value: 'pending_reason', label: 'En attente de motif' },
  { value: 'unjustified', label: 'Non justifiee' },
];

const absencePeriodFilterOptions = [
  { value: '', label: 'Toutes les periodes' },
  { value: 'current_month', label: 'Ce mois-ci' },
  { value: 'current_quarter', label: 'Ce trimestre' },
  { value: 'current_year', label: 'Cette annee' },
];

function resolveAbsenceStatusKey(item) {
  if (item?.is_justified) {
    return 'justified';
  }

  const hasReason = Boolean(String(item?.reason || item?.justification || '').trim());
  if (!hasReason) {
    return 'pending_reason';
  }

  return 'unjustified';
}

function resolveAbsenceStatus(item) {
  if (item?.is_justified) {
    return { label: 'Justifiee', tone: 'success' };
  }

  const hasReason = Boolean(String(item?.reason || item?.justification || '').trim());
  if (!hasReason) {
    return { label: 'En attente de motif', tone: 'warning' };
  }

  return { label: 'Non justifiee', tone: 'danger' };
}

function resolveAbsenceCalendarColor(item) {
  if (item?.is_justified === true) {
    return '#22C55E';
  }

  if (item?.is_justified === false) {
    return '#EF4444';
  }

  return '#F59E0B';
}

function formatDateValue(value) {
  if (!value) {
    return '-';
  }

  const parsed = new Date(value);
  if (Number.isNaN(parsed.getTime())) {
    return String(value);
  }

  return parsed.toLocaleDateString('fr-FR');
}

function resolveStudentDisplayName(item) {
  const firstName = String(item?.student?.first_name || '').trim();
  const lastName = String(item?.student?.last_name || '').trim();
  const fullName = `${firstName} ${lastName}`.trim();

  if (fullName !== '') {
    return fullName;
  }

  return item?.student?.name || item?.student_name || 'Eleve';
}

export function AbsencesPage() {
  const { user } = useAuth();
  const role = user?.role;
  const [studentFieldOptions, setStudentFieldOptions] = useState([{ value: '', label: 'Selectionner un eleve' }]);
  const [classFieldOptions, setClassFieldOptions] = useState([{ value: '', label: 'Selectionner une classe' }]);
  const [subjectFieldOptions, setSubjectFieldOptions] = useState([{ value: '', label: 'Selectionner une matiere' }]);
  const [teacherFieldOptions, setTeacherFieldOptions] = useState([{ value: '', label: 'Selectionner un enseignant' }]);
  const [classFilterOptions, setClassFilterOptions] = useState([{ value: '', label: 'Toutes les classes' }]);
  const [viewMode, setViewMode] = useState('table');
  const [calendarItems, setCalendarItems] = useState([]);
  const [isCalendarLoading, setIsCalendarLoading] = useState(false);
  const [calendarError, setCalendarError] = useState('');
  const [selectedCalendarAbsence, setSelectedCalendarAbsence] = useState(null);

  const service = useMemo(
    () => createAbsencesService(role || ROLES.ADMIN),
    [role]
  );

  const canMutate = role === ROLES.ADMIN || role === ROLES.TEACHER;

  const loadCalendarItems = useCallback(async () => {
    setIsCalendarLoading(true);
    setCalendarError('');

    try {
      const payload = await service.list({ page: 1, per_page: 500 });
      const items = parseListResponse(payload?.data || payload).items;
      setCalendarItems(items);
    } catch (error) {
      setCalendarError(error?.message || 'Impossible de charger les absences pour le calendrier.');
    } finally {
      setIsCalendarLoading(false);
    }
  }, [service]);

  useEffect(() => {
    if (viewMode !== 'calendar') {
      return;
    }

    loadCalendarItems();
  }, [viewMode, loadCalendarItems]);

  const calendarAbsences = useMemo(
    () => calendarItems.map((absence) => {
      const studentName = resolveStudentDisplayName(absence);
      const subjectName = absence.subject?.name ?? 'Non precise';

      return {
        id: absence.id,
        title: `${studentName} - ${subjectName}`,
        start: absence.absence_date,
        allDay: true,
        color: resolveAbsenceCalendarColor(absence),
        extendedProps: {
          student: studentName,
          subject: subjectName,
          className: absence.class?.name || absence.class_name || '-',
          reason: absence.reason || '-',
          type: absence.type || '-',
          justified: absence.is_justified,
        },
      };
    }),
    [calendarItems]
  );

  const handleCalendarEventClick = useCallback((clickInfo) => {
    const { event } = clickInfo;

    setSelectedCalendarAbsence({
      id: event.id,
      title: event.title,
      start: event.start,
      ...event.extendedProps,
    });
  }, []);

  useEffect(() => {
    if (!canMutate) {
      return;
    }

    const loadFormOptions = async () => {
      try {
        const usersEndpoint = role === ROLES.ADMIN
          ? '/api/v1/admin/users'
          : '/api/v1/teacher/students';
        const classesEndpoint = role === ROLES.ADMIN
          ? '/api/v1/admin/classes'
          : '/api/v1/teacher/classes';
        const subjectsEndpoint = role === ROLES.ADMIN
          ? '/api/v1/admin/subjects'
          : '/api/v1/teacher/subjects';

        const [studentsRes, classesRes, subjectsRes] = await Promise.all([
          apiClient.get(usersEndpoint, { params: { role: 'student', per_page: 500 } }),
          apiClient.get(classesEndpoint, { params: { per_page: 500 } }),
          apiClient.get(subjectsEndpoint, { params: { per_page: 500 } }),
        ]);

        const students = parseListResponse(studentsRes.data?.data || studentsRes.data).items;
        const classes = parseListResponse(classesRes.data?.data || classesRes.data).items;
        const subjects = parseListResponse(subjectsRes.data?.data || subjectsRes.data).items;

        setStudentFieldOptions([
          { value: '', label: 'Selectionner un eleve' },
          ...students.map((student) => ({ value: String(student.id), label: student.name })),
        ]);

        setClassFieldOptions([
          { value: '', label: 'Selectionner une classe' },
          ...classes.map((classItem) => ({ value: String(classItem.id), label: classItem.name })),
        ]);

        setSubjectFieldOptions([
          { value: '', label: 'Selectionner une matiere' },
          ...subjects.map((subject) => ({ value: String(subject.id), label: subject.name })),
        ]);

        if (role === ROLES.ADMIN) {
          const teachersRes = await apiClient.get('/api/v1/admin/users', {
            params: { role: 'teacher', per_page: 500 },
          });
          const teachers = parseListResponse(teachersRes.data?.data || teachersRes.data).items;

          setTeacherFieldOptions([
            { value: '', label: 'Selectionner un enseignant' },
            ...teachers.map((teacher) => ({ value: String(teacher.id), label: teacher.name })),
          ]);
        } else {
          setTeacherFieldOptions([
            { value: '', label: 'Selectionner un enseignant' },
            { value: String(user?.id || ''), label: user?.name || 'Enseignant' },
          ]);
        }
      } catch {
        setStudentFieldOptions([{ value: '', label: 'Selectionner un eleve' }]);
        setClassFieldOptions([{ value: '', label: 'Selectionner une classe' }]);
        setSubjectFieldOptions([{ value: '', label: 'Selectionner une matiere' }]);
        setTeacherFieldOptions([{ value: '', label: 'Selectionner un enseignant' }]);
      }
    };

    loadFormOptions();
  }, [canMutate, role, user?.id, user?.name]);

  useEffect(() => {
    const nextOptions = [
      { value: '', label: 'Toutes les classes' },
      ...classFieldOptions
        .filter((option) => option.value)
        .map((option) => ({ value: option.value, label: option.label })),
    ];

    setClassFilterOptions((currentOptions) => {
      const currentSignature = JSON.stringify(currentOptions);
      const nextSignature = JSON.stringify(nextOptions);
      return currentSignature === nextSignature ? currentOptions : nextOptions;
    });
  }, [classFieldOptions]);

  const absenceFilters = useMemo(
    () => [
      {
        name: 'class_id',
        label: 'Classe',
        options: classFilterOptions,
        defaultValue: '',
      },
      {
        name: 'status',
        label: 'Statut',
        options: absenceStatusFilterOptions,
        defaultValue: '',
      },
      {
        name: 'period',
        label: 'Periode',
        options: absencePeriodFilterOptions,
        defaultValue: '',
      },
    ],
    [classFilterOptions]
  );

  const buildAbsenceListParams = useCallback(
    ({ search, page }) => ({
      search,
      page,
      per_page: 500,
    }),
    []
  );

  const handleListLoaded = useCallback((listPayload) => {
    const parsedItems = parseListResponse(listPayload).items;
    const classMap = new Map();

    parsedItems.forEach((item) => {
      const classId = String(item.class_id || item.class?.id || '').trim();
      if (!classId) {
        return;
      }

      const classLabel = item.class?.name || item.class_name || `Classe ${classId}`;
      classMap.set(classId, classLabel);
    });

    if (classMap.size === 0) {
      return;
    }

    const nextOptions = [
      { value: '', label: 'Toutes les classes' },
      ...Array.from(classMap.entries()).map(([value, label]) => ({ value, label })),
    ];

    setClassFilterOptions((currentOptions) => {
      if (currentOptions.length > 1) {
        return currentOptions;
      }

      const currentSignature = JSON.stringify(currentOptions);
      const nextSignature = JSON.stringify(nextOptions);
      return currentSignature === nextSignature ? currentOptions : nextOptions;
    });
  }, []);

  const applyClientFilters = useCallback((loadedItems, activeFilters) => {
    const now = new Date();
    const currentMonth = now.getMonth();
    const currentYear = now.getFullYear();
    const currentQuarter = Math.floor(currentMonth / 3);

    return loadedItems.filter((item) => {
      if (activeFilters.class_id) {
        const itemClassId = String(item.class_id || item.class?.id || '');
        if (itemClassId !== activeFilters.class_id) {
          return false;
        }
      }

      if (activeFilters.status) {
        const itemStatus = resolveAbsenceStatusKey(item);
        if (itemStatus !== activeFilters.status) {
          return false;
        }
      }

      if (activeFilters.period) {
        const absenceDate = new Date(item.absence_date || item.date || '');
        if (Number.isNaN(absenceDate.getTime())) {
          return false;
        }

        const absenceYear = absenceDate.getFullYear();
        const absenceMonth = absenceDate.getMonth();
        const absenceQuarter = Math.floor(absenceMonth / 3);

        if (activeFilters.period === 'current_month' && (absenceYear !== currentYear || absenceMonth !== currentMonth)) {
          return false;
        }

        if (activeFilters.period === 'current_quarter' && (absenceYear !== currentYear || absenceQuarter !== currentQuarter)) {
          return false;
        }

        if (activeFilters.period === 'current_year' && absenceYear !== currentYear) {
          return false;
        }
      }

      return true;
    });
  }, []);

  return (
    <div className="space-y-4">
      <div className="surface-card flex flex-wrap items-center justify-between gap-3 p-4">
        <div className="flex items-center gap-2">
          <Button
            variant={viewMode === 'table' ? 'primary' : 'secondary'}
            onClick={() => setViewMode('table')}
          >
            Tableau
          </Button>
          <Button
            variant={viewMode === 'calendar' ? 'primary' : 'secondary'}
            onClick={() => setViewMode('calendar')}
          >
            Calendrier
          </Button>
        </div>

        {viewMode === 'calendar' ? (
          <Button variant="secondary" onClick={loadCalendarItems}>
            Rafraichir
          </Button>
        ) : null}
      </div>

      {viewMode === 'table' ? (
        <CrudPage
          title="Absences"
          description="Suivi de presence et justification"
          service={service}
          createLabel="Nouvelle absence"
          canCreate={canMutate}
          canEdit={canMutate}
          canDelete={canMutate}
          filters={absenceFilters}
          buildListParams={buildAbsenceListParams}
          includeFiltersInRequest={false}
          onListLoaded={handleListLoaded}
          applyClientFilters={applyClientFilters}
          columns={[
            {
              key: 'student_name',
              label: FR.tables.absences.student,
              render: (item) => item.student?.name || item.student_name || item.student_id || '-',
            },
            {
              key: 'class_name',
              label: FR.tables.absences.class,
              render: (item) => item.class?.name || item.class_name || item.class_id || '-',
            },
            {
              key: 'type',
              label: FR.tables.absences.type,
              render: (item) => <Badge tone="brand">{item.type || '-'}</Badge>,
            },
            { key: 'absence_date', label: FR.tables.absences.date, format: 'date' },
            {
              key: 'is_justified',
              label: FR.tables.absences.justified,
              render: (item) => {
                const status = resolveAbsenceStatus(item);

                return <Badge tone={status.tone}>{status.label}</Badge>;
              },
            },
            { key: 'reason', label: FR.tables.absences.reason },
          ]}
          fields={[
            { name: 'student_id', label: 'Eleve', type: 'select', required: true, options: studentFieldOptions },
            { name: 'class_id', label: 'Classe', type: 'select', required: true, options: classFieldOptions },
            { name: 'subject_id', label: 'Matiere', type: 'select', options: subjectFieldOptions },
            { name: 'recorded_by', label: 'Enseignant', type: 'select', options: teacherFieldOptions },
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
            student_id: String(item.student_id || item.student?.id || ''),
            class_id: String(item.class_id || item.class?.id || ''),
            subject_id: String(item.subject_id || item.subject?.id || ''),
            recorded_by: String(item.recorded_by || item.recordedBy?.id || ''),
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
            title: 'Aucune absence enregistree',
            description: 'Suivez les absences pour maintenir un historique de presence fiable.',
            actionLabel: 'Ajouter une absence',
          }}
        />
      ) : (
        <>
          {calendarError ? (
            <div className="surface-card p-4" style={{ color: '#dc2626' }}>
              {calendarError}
            </div>
          ) : null}

          {isCalendarLoading ? (
            <div className="surface-card p-6" style={{ color: 'var(--color-muted)', fontSize: '14px' }}>
              Chargement du calendrier...
            </div>
          ) : (
            <CalendarView events={calendarAbsences} onEventClick={handleCalendarEventClick} />
          )}

          <Modal
            title={selectedCalendarAbsence?.title || 'Absence'}
            isOpen={Boolean(selectedCalendarAbsence)}
            onClose={() => setSelectedCalendarAbsence(null)}
            footer={
              <div className="flex justify-end">
                <Button variant="secondary" onClick={() => setSelectedCalendarAbsence(null)}>
                  Fermer
                </Button>
              </div>
            }
          >
            <div className="space-y-2 text-sm" style={{ color: 'var(--color-text)' }}>
              <p><strong>Eleve:</strong> {selectedCalendarAbsence?.student || '-'}</p>
              <p><strong>Matiere:</strong> {selectedCalendarAbsence?.subject || '-'}</p>
              <p><strong>Classe:</strong> {selectedCalendarAbsence?.className || '-'}</p>
              <p><strong>Date:</strong> {formatDateValue(selectedCalendarAbsence?.start)}</p>
              <p><strong>Type:</strong> {selectedCalendarAbsence?.type || '-'}</p>
              <p>
                <strong>Statut:</strong> {selectedCalendarAbsence?.justified === true
                  ? 'Justifiee'
                  : selectedCalendarAbsence?.justified === false
                    ? 'Non justifiee'
                    : 'En attente'}
              </p>
              <p><strong>Motif:</strong> {selectedCalendarAbsence?.reason || '-'}</p>
            </div>
          </Modal>
        </>
      )}
    </div>
  );
}
