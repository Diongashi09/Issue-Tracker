<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // ProjectPolicy::update() wired in next phase via authorizeResource()
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date'  => ['nullable', 'date'],
            'deadline'    => ['nullable', 'date', Rule::when($this->filled('start_date'), 'after_or_equal:start_date')],
        ];
    }
}
