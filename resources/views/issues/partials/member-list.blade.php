@php
    $assignedIds = $issue->assignees->pluck('id');
    $available   = $allUsers->whereNotIn('id', $assignedIds->all());
@endphp

<div class="d-flex flex-column gap-2">
    @forelse ($issue->assignees as $user)
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <div class="rounded-circle bg-secondary d-flex align-items-center justify-content-center
                            text-white fw-bold flex-shrink-0"
                     style="width:28px;height:28px;font-size:.7rem;"
                     aria-hidden="true">
                    {{ mb_strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <span class="small">{{ $user->name }}</span>
            </div>
            <button type="button"
                    class="btn btn-link btn-sm p-0 text-muted text-decoration-none lh-1"
                    data-destroy-url="{{ route('issues.members.destroy', [$issue, $user]) }}"
                    aria-label="Remove {{ $user->name }}">&times;</button>
        </div>
    @empty
        <span class="text-muted small fst-italic">No members assigned.</span>
    @endforelse
</div>

@if ($available->isNotEmpty())
    <form id="member-assign-form" class="mt-3" novalidate
          data-store-url="{{ route('issues.members.store', $issue) }}">
        <div class="d-flex gap-2">
            <select class="form-select form-select-sm" name="user_id" id="member-select">
                <option value="">Assign a member…</option>
                @foreach ($available as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="btn btn-outline-secondary btn-sm text-nowrap">Add</button>
        </div>
        {{-- Inline error shown by members.js on 422 --}}
        <div class="text-danger small mt-1 d-none" id="error-user_id" role="alert"></div>
    </form>
@endif
