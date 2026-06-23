<?php

namespace App\Http\Requests\Issue;

use App\Enums\IssuePriority;
use App\Enums\IssueStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreIssueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // IssuePolicy::create() gates this via authorizeResource()
    }

    public function rules(): array
    {
        return [
            // Scoped exists: project must belong to the authenticated user.
            // Prevents creating issues in other users' projects.
            'project_id'  => ['required', 'integer', Rule::exists('projects', 'id')->where('user_id', $this->user()->id)],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'status'      => ['required', Rule::enum(IssueStatus::class)],
            'priority'    => ['required', Rule::enum(IssuePriority::class)],
            'due_date'    => ['nullable', 'date'],
            // Tags are synced after create; each id must reference an existing tag.
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
