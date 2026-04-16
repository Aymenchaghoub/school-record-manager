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
        return in_array($this->user()?->role, ['admin', 'teacher'], true);
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
            'teacher_id' => ['nullable', 'exists:users,id'],
            'value' => ['required', 'numeric', 'min:0', 'max:20'],
            'max_value' => ['nullable', 'numeric', 'in:20'],
            'type' => ['required', 'in:exam,quiz,assignment,project,participation,midterm,final'],
            'title' => ['nullable', 'string', 'max:255'],
            'grade_date' => ['required', 'date'],
            'term' => ['nullable', 'string', 'max:255'],
            'weight' => ['nullable', 'numeric', 'gt:0'],
            'comment' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages.
     */
    public function messages(): array
    {
        return [
            'value.min' => 'La note ne peut pas etre negative.',
            'value.max' => 'La note ne peut pas depasser 20.',
        ];
    }
}
