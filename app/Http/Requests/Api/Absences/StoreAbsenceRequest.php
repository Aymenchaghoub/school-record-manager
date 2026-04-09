<?php

namespace App\Http\Requests\Api\Absences;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreAbsenceRequest extends FormRequest
{
    private const TYPES = [
        'full_day',
        'partial',
        'late_arrival',
        'early_departure',
    ];

    public function authorize(): bool
    {
        return in_array($this->user()?->role, ['admin', 'teacher'], true);
    }

    public function rules(): array
    {
        return [
            'student_id' => ['required', 'integer', Rule::exists('users', 'id')->where('role', 'student')],
            'class_id' => ['required', 'integer', 'exists:classes,id'],
            'subject_id' => ['nullable', 'integer', 'exists:subjects,id'],
            'recorded_by' => ['nullable', 'integer', Rule::exists('users', 'id')->where('role', 'teacher')],
            'absence_date' => ['required', 'date'],
            'start_time' => ['nullable', 'date_format:H:i'],
            'end_time' => ['nullable', 'date_format:H:i'],
            'is_justified' => ['sometimes', 'boolean'],
            'type' => ['required', Rule::in(self::TYPES)],
            'reason' => ['nullable', 'string', 'max:255'],
            'justification' => ['nullable', 'string'],
            'justification_document' => ['nullable', 'string', 'max:255'],
        ];
    }
}
