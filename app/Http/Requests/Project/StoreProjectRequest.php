<?php

namespace App\Http\Requests\Project;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreProjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // any authenticated user may create a project
    }

    public function rules(): array
    {
        return [
            'name'        => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'start_date'  => ['nullable', 'date'],
            // after_or_equal only enforced when start_date is actually present
            'deadline'    => ['nullable', 'date', Rule::when($this->filled('start_date'), 'after_or_equal:start_date')],
        ];
    }
}
