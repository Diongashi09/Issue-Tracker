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

            {{-- Comments — lazy-loaded on page paint, paginated via "Load older" (blueprint §10, §14). --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h6 mb-0">Comments</h2>
                </div>

                {{--
                  AJAX renders into this list.
                  aria-live: screen readers announce newly prepended comments.
                  data-index-url: comments.js reads this to know where to GET pages from.
                --}}
                <div id="comment-list"
                     class="list-group list-group-flush"
                     aria-live="polite"
                     aria-relevant="additions"
                     data-index-url="{{ route('issues.comments.index', $issue) }}">
                    <div class="list-group-item text-muted small fst-italic py-3">
                        Loading comments…
                    </div>
                </div>

                <div id="comment-load-more-container" class="d-none border-top text-center py-2">
                    <button type="button" id="comment-load-more"
                            class="btn btn-link btn-sm p-0 text-decoration-none text-muted">
                        Load older comments
                    </button>
                </div>

                <div class="card-footer bg-transparent">
                    <form id="comment-form" novalidate
                          data-store-url="{{ route('issues.comments.store', $issue) }}">
                        <div class="mb-2">
                            <label for="comment_author_name" class="form-label fw-medium small mb-1">
                                Your name
                            </label>
                            <input type="text" id="comment_author_name" name="author_name"
                                   class="form-control form-control-sm"
                                   placeholder="Display name" maxlength="255" autocomplete="name">
                            <div class="invalid-feedback" id="error-author_name"></div>
                        </div>
                        <div class="mb-2">
                            <label for="comment_body" class="form-label fw-medium small mb-1">
                                Comment
                            </label>
                            <textarea id="comment_body" name="body"
                                      class="form-control form-control-sm" rows="3"
                                      placeholder="Write a comment…" maxlength="5000"></textarea>
                            <div class="invalid-feedback" id="error-body"></div>
                        </div>
                        <button type="submit" id="comment-submit" class="btn btn-primary btn-sm">
                            Add Comment
                        </button>
                    </form>
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
