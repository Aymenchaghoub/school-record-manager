<?php

namespace App\Http\Requests\Api\Classes;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClassRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->role === 'admin';
    }

    public function rules(): array
    {
        $classId = $this->route('class')?->id;

        return [
            'name' => ['sometimes', 'required', 'string', 'max:255'],
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:100',
                Rule::unique('classes', 'code')->ignore($classId),
            ],
            'level' => ['sometimes', 'required', 'string', 'max:100'],
            'section' => ['nullable', 'string', 'max:50'],
            'academic_year' => ['sometimes', 'required', 'string', 'max:30'],
            'responsible_teacher_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'teacher')],
            'teacher_id' => ['nullable', Rule::exists('users', 'id')->where('role', 'teacher')],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:500'],
            'description' => ['nullable', 'string'],
            'is_active' => ['sometimes', 'boolean'],
        ];
    }
}
