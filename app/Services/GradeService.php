<?php

namespace App\Services;

use App\Models\Grade;
use Illuminate\Database\Eloquent\Builder;

class GradeService
{
    /**
     * Crée une note avec les valeurs normalisées du schéma courant.
     */
    public function create(array $data): Grade
    {
        $payload = $this->normalizePayload($data);

        return Grade::create($payload);
    }

    /**
     * Met a jour une note avec les valeurs normalisées du schéma courant.
     */
    public function update(Grade $grade, array $data): Grade
    {
        $payload = $this->normalizePayload($data);

        $grade->update($payload);

        return $grade->fresh();
    }

    /**
     * Calcule la moyenne d'un etudiant pour une matiere donnee.
     */
    public function averageForStudent(int $studentId, int $subjectId): float
    {
        $grades = Grade::where('student_id', $studentId)
            ->where('subject_id', $subjectId)
            ->get();

        if ($grades->isEmpty()) {
            return 0.0;
        }

        return round((float) $grades->avg('value'), 2);
    }

    /**
     * Calcule la moyenne globale d'un etudiant sur l'ensemble des matieres.
     */
    public function overallAverageForStudent(int $studentId): float
    {
        $average = Grade::where('student_id', $studentId)->avg('value');

        return round((float) ($average ?? 0), 2);
    }

    /**
     * Calcule les statistiques d'une classe pour une matiere.
     */
    public function classStats(int $classId, int $subjectId): array
    {
        $grades = Grade::query()
            ->where('subject_id', $subjectId)
            ->whereHas('student', function (Builder $query) use ($classId) {
                $query->whereHas('studentClasses', function (Builder $classQuery) use ($classId) {
                    $classQuery->where('classes.id', $classId);
                });
            })
            ->pluck('value');

        if ($grades->isEmpty()) {
            return ['average' => 0, 'min' => 0, 'max' => 0, 'count' => 0];
        }

        return [
            'average' => round((float) $grades->avg(), 2),
            'min' => (float) $grades->min(),
            'max' => (float) $grades->max(),
            'count' => $grades->count(),
        ];
    }

    /**
     * Retourne le label de performance selon la valeur (sur 20).
     */
    public function performanceLabel(float $value): string
    {
        return match (true) {
            $value >= 16 => 'Excellent',
            $value >= 12 => 'Bien',
            $value >= 10 => 'Passable',
            default => 'Insuffisant',
        };
    }

    /**
     * Crée plusieurs notes sur une meme evaluation.
     */
    public function batchCreate(array $data, int $teacherId): void
    {
        foreach ($data['grades'] as $gradeData) {
            $payload = $this->normalizePayload([
                'student_id' => $gradeData['student_id'],
                'subject_id' => $data['subject_id'],
                'class_id' => $data['class_id'],
                'teacher_id' => $teacherId,
                'type' => $data['type'],
                'value' => $gradeData['value'],
                'max_value' => $gradeData['max_value'] ?? 20,
                'title' => $data['title'] ?? null,
                'grade_date' => $data['grade_date'] ?? now()->toDateString(),
                'term' => $data['term'] ?? null,
                'weight' => $data['weight'] ?? 1,
                'comment' => $gradeData['comment'] ?? null,
            ]);

            Grade::create($payload);
        }
    }

    private function normalizePayload(array $data): array
    {
        if (isset($data['value'])) {
            $data['value'] = (float) $data['value'];
        }

        $data['max_value'] = 20;

        if (array_key_exists('weight', $data)) {
            $data['weight'] = (float) ($data['weight'] ?: 1);
        } else {
            $data['weight'] = 1;
        }

        return $data;
    }
}
