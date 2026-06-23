<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-start justify-content-between flex-wrap gap-2">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1">
                    <a href="{{ route('projects.show', $issue->project) }}"
                       class="text-decoration-none text-muted">&larr;</a>
                    <h1 class="h4 mb-0">{{ $issue->title }}</h1>
                </div>
                <div class="d-flex align-items-center flex-wrap gap-2 ms-4">
                    <x-status-badge :status="$issue->status" />
                    <x-priority-badge :priority="$issue->priority" />
                    <span class="text-muted small">
                        in
                        <a href="{{ route('projects.show', $issue->project) }}"
                           class="text-decoration-none">{{ $issue->project->name }}</a>
                    </span>
                    @if ($issue->due_date)
                        @php
                            $overdue = $issue->due_date->isPast()
                                && $issue->status !== \App\Enums\IssueStatus::Closed;
                        @endphp
                        <span class="text-muted small">&middot;</span>
                        <span class="small {{ $overdue ? 'text-danger' : 'text-muted' }}">
                            Due {{ $issue->due_date->format('M j, Y') }}
                            @if ($overdue) <strong>(overdue)</strong> @endif
                        </span>
                    @endif
                </div>
            </div>

            {{-- Phase 5 gates these with @can('update'/'delete', $issue). --}}
            <div class="d-flex gap-2">
                <a href="{{ route('issues.edit', $issue) }}" class="btn btn-outline-secondary btn-sm">
                    Edit Issue
                </a>
                <form method="POST" action="{{ route('issues.destroy', $issue) }}" class="d-inline"
                      onsubmit="return confirm('Delete this issue?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                </form>
            </div>
        </div>
    </x-slot>

    <div class="row g-4">
        {{-- Main column: description + comments --}}
        <div class="col-lg-8">
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h6 mb-0">Description</h2>
                </div>
                <div class="card-body">
                    @if ($issue->description)
                        <p class="mb-0" style="white-space: pre-line;">{{ $issue->description }}</p>
                    @else
                        <p class="mb-0 text-muted fst-italic">No description provided.</p>
                    @endif
                </div>
            </div>

            {{-- Comments — placeholder. Phase 4 lazy-loads paginated comments here via AJAX. --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h6 mb-0">Comments</h2>
                </div>
                <div class="card-body" id="comments-section" data-issue-id="{{ $issue->id }}">
                    <p class="mb-0 text-muted small">Comments load here in Phase 4.</p>
                </div>
            </div>
        </div>

        {{-- Sidebar: tags + members --}}
        <div class="col-lg-4">
            {{-- Tags — placeholder. Phase 4 wires attach/detach via AJAX. --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h6 mb-0">Tags</h2>
                </div>
                <div class="card-body" id="tags-section" data-issue-id="{{ $issue->id }}">
                    <p class="mb-0 text-muted small">Tag management arrives in Phase 4.</p>
                </div>
            </div>

            {{-- Members — placeholder. Phase 4 wires assign/unassign via AJAX. --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h6 mb-0">Assigned Members</h2>
                </div>
                <div class="card-body" id="members-section" data-issue-id="{{ $issue->id }}">
                    <p class="mb-0 text-muted small">Member assignment arrives in Phase 4.</p>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
