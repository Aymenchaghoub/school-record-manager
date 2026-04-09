<?php

namespace App\Http\Requests\Api\Grades;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGradeRequest extends FormRequest
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
            'student_id' => ['sometimes', 'required', 'integer', Rule::exists('users', 'id')->where('role', 'student')],
            'subject_id' => ['sometimes', 'required', 'integer', 'exists:subjects,id'],
            'class_id' => ['sometimes', 'required', 'integer', 'exists:classes,id'],
            'teacher_id' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'teacher')],
            'value' => ['sometimes', 'required', 'numeric', 'min:0'],
            'max_value' => ['nullable', 'numeric', 'gt:0'],
            'type' => ['sometimes', 'required', Rule::in(self::TYPES)],
            'title' => ['nullable', 'string', 'max:255'],
            'grade_date' => ['sometimes', 'required', 'date'],
            'term' => ['nullable', 'string', 'max:100'],
            'weight' => ['nullable', 'numeric', 'gt:0'],
            'comment' => ['nullable', 'string'],
        ];
    }
}
