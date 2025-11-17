<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreGradeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return in_array($this->user()->role, ['admin', 'teacher']);
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'student_id' => ['required', 'exists:users,id'],
            'subject_id' => ['required', 'exists:subjects,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'grade' => ['required', 'numeric', 'min:0', 'max:100'],
            'exam_type' => ['required', 'string', 'max:255'],
            'semester' => ['required', 'integer', 'min:1', 'max:2'],
            'academic_year' => ['required', 'string', 'max:255'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'grade.min' => 'Grade cannot be negative.',
            'grade.max' => 'Grade cannot exceed 100.',
            'semester.min' => 'Invalid semester number.',
            'semester.max' => 'Invalid semester number.',
        ];
    }
}
