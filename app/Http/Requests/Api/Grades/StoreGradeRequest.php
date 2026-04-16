<?php

namespace App\Http\Requests\Api\Grades;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGradeRequest extends FormRequest
{
    private const TYPES = [
        'exam',
        'quiz',
        'assignment',
        'project',
        'participation',
        'midterm',
        'final',
    ];

    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'teacher'], true);
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'student')],
            'subject_id' => ['required', 'integer', 'exists:subjects,id'],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'teacher')],
            'value' => ['required', 'numeric', 'min:0', 'max:20'],
            'max_value' => ['nullable', 'numeric', 'in:20'],
            'type' => ['required', Rule::in(self::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'grade_date' => ['required', 'date'],
            'term' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'gt:0'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
