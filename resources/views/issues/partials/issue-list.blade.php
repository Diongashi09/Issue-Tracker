{{--
  Shared partial — used in projects.show (Phase 2) and issues.index (Phase 3).
  Receives: $issues (LengthAwarePaginator with tags, assignees, comments_count eager-loaded).
  Phase 4 will make this the AJAX swap target for search + filter responses.
--}}
<div class="table-responsive">
    <table class="table table-hover align-middle mb-0">
        <thead class="table-light">
            <tr>
                <th>Title</th>
                <th class="text-nowrap">Status</th>
                <th class="text-nowrap">Priority</th>
                <th class="text-nowrap">Due Date</th>
                <th class="text-end text-nowrap">Activity</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($issues as $issue)
                <tr>
                    <td style="max-width: 420px;">
                        @if (Route::has('issues.show'))
                            <a href="{{ route('issues.show', $issue) }}"
                               class="text-decoration-none fw-medium text-body">
                                {{ $issue->title }}
                            </a>
                        @else
                            <span class="fw-medium">{{ $issue->title }}</span>
                        @endif

                        @if ($issue->tags->isNotEmpty())
                            <div class="mt-1 d-flex flex-wrap gap-1">
                                @foreach ($issue->tags as $tag)
                                    <x-tag-badge :tag="$tag" />
                                @endforeach
                            </div>
                        @endif
                    </td>

                    <td><x-status-badge :status="$issue->status" /></td>

                    <td><x-priority-badge :priority="$issue->priority" /></td>

                    <td class="text-nowrap">
                        @if ($issue->due_date)
                            @php
                                $overdue = $issue->due_date->isPast()
                                    && $issue->status !== \App\Enums\IssueStatus::Closed;
                            @endphp
                            <span class="{{ $overdue ? 'text-danger' : 'text-muted' }} small">
                                {{ $issue->due_date->format('M j, Y') }}
                                @if ($overdue) <strong>(overdue)</strong> @endif
                            </span>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </td>

                    <td class="text-end text-nowrap">
                        <span class="text-muted small">
                            {{ $issue->assignees->count() }}
                            {{ Str::plural('assignee', $issue->assignees->count()) }}
                            &middot;
                            {{ $issue->comments_count }}
                            {{ Str::plural('comment', $issue->comments_count) }}
                        </span>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="text-center text-muted py-5">
                        No issues yet.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </table>
</div>
