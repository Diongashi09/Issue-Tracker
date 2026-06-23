<?php

namespace App\Http\Requests\Tag;

use Illuminate\Foundation\Http\FormRequest;

class StoreTagRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'  => ['required', 'string', 'max:255', 'unique:tags,name'],
            // Accept a 7-char hex string — #rrggbb. nullable so callers may omit color.
            'color' => ['nullable', 'string', 'size:7', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }
}
