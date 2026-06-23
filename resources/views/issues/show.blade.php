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

            @canany(['update', 'delete'], $issue)
            <div class="d-flex gap-2">
                @can('update', $issue)
                    <a href="{{ route('issues.edit', $issue) }}" class="btn btn-outline-secondary btn-sm">
                        Edit Issue
                    </a>
                @endcan

                @can('delete', $issue)
                    <form method="POST" action="{{ route('issues.destroy', $issue) }}" class="d-inline"
                          onsubmit="return confirm('Delete this issue?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                @endcan
            </div>
            @endcanany
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
            {{-- Tags — server-rendered on first paint; AJAX swaps #tags-section innerHTML on attach/detach. --}}
            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h6 mb-0">Tags</h2>
                </div>
                <div class="card-body" id="tags-section">
                    @include('issues.partials.tag-list', ['issue' => $issue, 'allTags' => $allTags])
                </div>
            </div>

            {{-- Members — server-rendered on first paint; AJAX swaps #members-section innerHTML on assign/unassign. --}}
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h6 mb-0">Assigned Members</h2>
                </div>
                <div class="card-body" id="members-section">
                    @include('issues.partials.member-list', ['issue' => $issue, 'allUsers' => $allUsers])
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
