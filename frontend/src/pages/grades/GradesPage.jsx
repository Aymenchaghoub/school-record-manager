import { useCallback, useEffect, useMemo, useState } from 'react';
import { CrudPage } from '../../components/common/CrudPage';
import { Badge } from '../../components/ui/Badge';
import { useAuth } from '../../hooks/useAuth';
import FR from '../../i18n/fr';
import apiClient from '../../services/apiClient';
import { createGradesService } from '../../services/gradesService';
import { ROLES } from '../../utils/constants';
import { parseListResponse } from '../../utils/response';

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
  const [subjectFilterOptions, setSubjectFilterOptions] = useState([
    { value: '', label: 'Toutes les matieres' },
  ]);
  const [classFilterOptions, setClassFilterOptions] = useState([
    { value: '', label: 'Toutes les classes' },
  ]);
  const [termFilterOptions, setTermFilterOptions] = useState([
    { value: '', label: 'Toutes les periodes' },
  ]);
  const [studentFieldOptions, setStudentFieldOptions] = useState([{ value: '', label: 'Selectionner un eleve' }]);
  const [subjectFieldOptions, setSubjectFieldOptions] = useState([{ value: '', label: 'Selectionner une matiere' }]);
  const [classFieldOptions, setClassFieldOptions] = useState([{ value: '', label: 'Selectionner une classe' }]);
  const [teacherFieldOptions, setTeacherFieldOptions] = useState([{ value: '', label: 'Selectionner un enseignant' }]);

  const service = useMemo(
    () => createGradesService(role || ROLES.ADMIN),
    [role]
  );

  const canMutate = role === ROLES.ADMIN || role === ROLES.TEACHER;

  const gradeFilters = useMemo(
    () => [
      {
        name: 'subject',
        label: 'Matiere',
        options: subjectFilterOptions,
        defaultValue: '',
      },
      {
        name: 'class',
        label: 'Classe',
        options: classFilterOptions,
        defaultValue: '',
      },
      {
        name: 'term',
        label: 'Periode',
        options: termFilterOptions,
        defaultValue: '',
      },
    ],
    [classFilterOptions, subjectFilterOptions, termFilterOptions]
  );

  useEffect(() => {
    if (!canMutate) {
      return;
    }

    const loadFormOptions = async () => {
      try {
        const studentsEndpoint = role === ROLES.ADMIN
          ? '/api/v1/admin/users'
          : '/api/v1/teacher/students';
        const classesEndpoint = role === ROLES.ADMIN
          ? '/api/v1/admin/classes'
          : '/api/v1/teacher/classes';
        const subjectsEndpoint = role === ROLES.ADMIN
          ? '/api/v1/admin/subjects'
          : '/api/v1/teacher/subjects';

        const [studentsRes, classesRes, subjectsRes] = await Promise.all([
          apiClient.get(studentsEndpoint, { params: { role: 'student', per_page: 500 } }),
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
    const nextSubjectOptions = [
      { value: '', label: 'Toutes les matieres' },
      ...subjectFieldOptions
        .filter((option) => option.value)
        .map((option) => ({ value: option.value, label: option.label })),
    ];

    const nextClassOptions = [
      { value: '', label: 'Toutes les classes' },
      ...classFieldOptions
        .filter((option) => option.value)
        .map((option) => ({ value: option.value, label: option.label })),
    ];

    setSubjectFilterOptions((currentOptions) => {
      const currentSignature = JSON.stringify(currentOptions);
      const nextSignature = JSON.stringify(nextSubjectOptions);
      return currentSignature === nextSignature ? currentOptions : nextSubjectOptions;
    });

    setClassFilterOptions((currentOptions) => {
      const currentSignature = JSON.stringify(currentOptions);
      const nextSignature = JSON.stringify(nextClassOptions);
      return currentSignature === nextSignature ? currentOptions : nextClassOptions;
    });
  }, [classFieldOptions, subjectFieldOptions]);

  const handleListLoaded = useCallback((listPayload) => {
    const items = parseListResponse(listPayload).items;
    const subjectMap = new Map();
    const classMap = new Map();
    const termMap = new Map();

    items.forEach((item) => {
      const subjectId = String(item.subject_id || item.subject?.id || '').trim();
      const subjectLabel = String(item.subject?.name || item.subject_name || '').trim();
      if (subjectId) {
        subjectMap.set(subjectId, subjectLabel || `Matiere ${subjectId}`);
      } else if (subjectLabel) {
        subjectMap.set(subjectLabel, subjectLabel);
      }

      const classId = String(item.class_id || item.class?.id || '').trim();
      const classLabel = String(item.class?.name || item.class_name || '').trim();
      if (classId) {
        classMap.set(classId, classLabel || `Classe ${classId}`);
      } else if (classLabel) {
        classMap.set(classLabel, classLabel);
      }

      const termValue = String(item.term || '').trim();
      if (termValue) {
        termMap.set(termValue, termValue);
      }
    });

    const subjectsFromPayload = Array.isArray(listPayload?.subjects) ? listPayload.subjects : [];
    subjectsFromPayload.forEach((subjectName) => {
      const normalized = String(subjectName || '').trim();
      if (normalized) {
        subjectMap.set(normalized, normalized);
      }
    });

    if (subjectMap.size === 0 && classMap.size === 0 && termMap.size === 0) {
      return;
    }

    const nextSubjectOptions = [
      { value: '', label: 'Toutes les matieres' },
      ...Array.from(subjectMap.entries()).map(([value, label]) => ({
        value,
        label,
      })),
    ];

    const nextClassOptions = [
      { value: '', label: 'Toutes les classes' },
      ...Array.from(classMap.entries()).map(([value, label]) => ({
        value,
        label,
      })),
    ];

    const nextTermOptions = [
      { value: '', label: 'Toutes les periodes' },
      ...Array.from(termMap.entries()).map(([value, label]) => ({
        value,
        label,
      })),
    ];

    setSubjectFilterOptions((currentOptions) => {
      if (currentOptions.length > 1) {
        return currentOptions;
      }

      const currentSignature = JSON.stringify(currentOptions);
      const nextSignature = JSON.stringify(nextSubjectOptions);
      return currentSignature === nextSignature ? currentOptions : nextSubjectOptions;
    });

    setClassFilterOptions((currentOptions) => {
      if (currentOptions.length > 1) {
        return currentOptions;
      }

      const currentSignature = JSON.stringify(currentOptions);
      const nextSignature = JSON.stringify(nextClassOptions);
      return currentSignature === nextSignature ? currentOptions : nextClassOptions;
    });

    setTermFilterOptions((currentOptions) => {
      if (currentOptions.length > 1) {
        return currentOptions;
      }

      const currentSignature = JSON.stringify(currentOptions);
      const nextSignature = JSON.stringify(nextTermOptions);
      return currentSignature === nextSignature ? currentOptions : nextTermOptions;
    });
  }, []);

  const applyClientFilters = useCallback((loadedItems, activeFilters) => loadedItems.filter((item) => {
    if (activeFilters.subject) {
      const selectedSubject = String(activeFilters.subject).trim().toLowerCase();
      const subjectId = String(item.subject_id || item.subject?.id || '').trim();
      const subjectName = String(item.subject?.name || item.subject_name || '').trim().toLowerCase();

      if (subjectId !== activeFilters.subject && subjectName !== selectedSubject) {
        return false;
      }
    }

    if (activeFilters.class) {
      const selectedClass = String(activeFilters.class).trim().toLowerCase();
      const classId = String(item.class_id || item.class?.id || '').trim();
      const className = String(item.class?.name || item.class_name || '').trim().toLowerCase();

      if (classId !== activeFilters.class && className !== selectedClass) {
        return false;
      }
    }

    if (activeFilters.term) {
      const selectedTerm = String(activeFilters.term).trim().toLowerCase();
      const term = String(item.term || '').trim().toLowerCase();

      if (term !== selectedTerm) {
        return false;
      }
    }

    return true;
  }), []);

  const buildGradesListParams = useCallback(
    ({ search, page }) => ({
      search,
      page,
      per_page: 500,
    }),
    []
  );

  return (
    <CrudPage
      title="Notes"
      description="Evaluation academique par eleve, matiere et periode"
      service={service}
      createLabel="Nouvelle note"
      canCreate={canMutate}
      canEdit={canMutate}
      canDelete={canMutate}
      filters={gradeFilters}
      buildListParams={buildGradesListParams}
      includeFiltersInRequest={false}
      onListLoaded={handleListLoaded}
      applyClientFilters={applyClientFilters}
      columns={[
        {
          key: 'student_name',
          label: FR.tables.grades.student,
          render: (item) => item.student?.name || item.student_name || item.student_id || '-',
        },
        {
          key: 'subject_name',
          label: FR.tables.grades.subject,
          render: (item) => item.subject?.name || item.subject_name || item.subject_id || '-',
        },
        {
          key: 'class_name',
          label: FR.tables.grades.class,
          render: (item) => item.class?.name || item.class_name || item.class_id || '-',
        },
        {
          key: 'value',
          label: FR.tables.grades.grade,
          render: (item) => `${item.value ?? '-'} / 20`,
        },
        {
          key: 'type',
          label: FR.tables.grades.type,
          render: (item) => <Badge tone="brand">{item.type || '-'}</Badge>,
        },
        {
          key: 'term',
          label: FR.tables.grades.term || 'Periode',
          render: (item) => item.term || '-',
        },
        { key: 'grade_date', label: FR.tables.grades.date, format: 'date' },
      ]}
      fields={[
        { name: 'student_id', label: 'Eleve', type: 'select', required: true, options: studentFieldOptions },
        { name: 'subject_id', label: 'Matiere', type: 'select', required: true, options: subjectFieldOptions },
        { name: 'class_id', label: 'Classe', type: 'select', required: true, options: classFieldOptions },
        { name: 'teacher_id', label: 'Enseignant', type: 'select', options: teacherFieldOptions },
        { name: 'value', label: 'Valeur', type: 'number', required: true },
        { name: 'type', label: 'Type', type: 'select', required: true, options: gradeTypeOptions },
        { name: 'title', label: 'Titre' },
        { name: 'grade_date', label: 'Date de note', type: 'date', required: true },
        { name: 'term', label: 'Periode' },
        { name: 'weight', label: 'Coefficient', type: 'number', defaultValue: 1 },
        { name: 'comment', label: 'Commentaire', type: 'textarea' },
      ]}
      mapItemToForm={(item) => ({
        ...item,
        student_id: String(item.student_id || item.student?.id || ''),
        subject_id: String(item.subject_id || item.subject?.id || ''),
        class_id: String(item.class_id || item.class?.id || ''),
        teacher_id: String(item.teacher_id || item.teacher?.id || ''),
      })}
      mapFormToPayload={(values) => ({
        ...values,
        student_id: Number(values.student_id),
        subject_id: Number(values.subject_id),
        class_id: Number(values.class_id),
        teacher_id: values.teacher_id ? Number(values.teacher_id) : null,
        value: Number(values.value),
        max_value: 20,
        weight: Number(values.weight || 1),
      })}
      emptyState={{
        title: 'Aucune note trouvee',
        description: 'Renseignez les evaluations pour suivre la progression scolaire.',
        actionLabel: 'Ajouter une note',
      }}
    />
  );
}
