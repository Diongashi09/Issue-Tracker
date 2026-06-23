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
        return true; // IssuePolicy::update() gates this via authorizeResource()
    }

    public function rules(): array
    {
        return [
            // Scoped exists: the target project must belong to the authenticated user.
            // Prevents moving an issue into another user's project.
            'project_id'  => ['required', 'integer', Rule::exists('projects', 'id')->where('user_id', $this->user()->id)],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', Rule::enum(IssueStatus::class)],
            'priority'    => ['required', Rule::enum(IssuePriority::class)],
            'due_date'    => ['nullable', 'date'],
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['integer', 'exists:tags,id'],
        ];
    }

    public function messages(): array
    {
        return [
            'project_id.exists' => 'The selected project does not exist or does not belong to you.',
        ];
    }
}
