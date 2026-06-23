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
        // Authorization (owner-of-parent-project) is wired in Phase 5 via IssuePolicy.
        return true;
    }

    public function rules(): array
    {
        return [
            'project_id'  => ['required', 'integer', 'exists:projects,id'],
            'title'       => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            // Rule::enum shares the same enum used for the cast — no duplicated string lists.
            'status'      => ['required', Rule::enum(IssueStatus::class)],
            'priority'    => ['required', Rule::enum(IssuePriority::class)],
            'due_date'    => ['nullable', 'date'],
            // Tags are synced after create; each id must reference an existing tag (prevents IDOR attachment).
            'tags'        => ['nullable', 'array'],
            'tags.*'      => ['integer', 'exists:tags,id'],
        ];
    }
}
