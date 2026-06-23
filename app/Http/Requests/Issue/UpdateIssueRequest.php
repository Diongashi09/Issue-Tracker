<?php

namespace App\Http\Requests\Issue;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        // IssuePolicy::update() wired in Phase 5 via authorizeResource().
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'  => ['required', 'integer', 'exists:projects,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', Rule::enum(IssueStatus::class)],
            'priority'    => ['required', Rule::enum(IssuePriority::class)],
            'due_date'    => ['nullable', 'date'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['integer', 'exists:tags,id'],
        ];
    }
}
