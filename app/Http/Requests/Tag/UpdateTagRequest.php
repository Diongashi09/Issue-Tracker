<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($this->tag)],
            'color' => ['nullable', 'string', 'size:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
