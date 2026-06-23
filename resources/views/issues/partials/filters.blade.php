@php
    $hasActiveFilter = collect($filters)->filter(fn ($v) => $v !== '' && $v !== null)->isNotEmpty();
@endphp
<div class="card shadow-sm mb-3">
    <div class="card-body py-2">
        <form id="filter-form"
              class="row g-2 align-items-end"
              data-index-url="{{ route('issues.index') }}"
              autocomplete="off">

            <div class="col-sm-6 col-lg-3">
                <label for="filter-status" class="form-label form-label-sm mb-1">Status</label>
                <select name="status" id="filter-status"
                        class="form-select form-select-sm filter-control">
                    <option value="">All statuses</option>
                    @foreach (\App\Enums\IssueStatus::cases() as $s)
                        <option value="{{ $s->value }}"
                                {{ ($filters['status'] ?? '') === $s->value ? 'selected' : '' }}>
                            {{ $s->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-lg-3">
                <label for="filter-priority" class="form-label form-label-sm mb-1">Priority</label>
                <select name="priority" id="filter-priority"
                        class="form-select form-select-sm filter-control">
                    <option value="">All priorities</option>
                    @foreach (\App\Enums\IssuePriority::cases() as $p)
                        <option value="{{ $p->value }}"
                                {{ ($filters['priority'] ?? '') === $p->value ? 'selected' : '' }}>
                            {{ $p->label() }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-lg-3">
                <label for="filter-tag" class="form-label form-label-sm mb-1">Tag</label>
                <select name="tag" id="filter-tag"
                        class="form-select form-select-sm filter-control">
                    <option value="">All tags</option>
                    @foreach ($allTags as $tag)
                        <option value="{{ $tag->id }}"
                                {{ ($filters['tag'] ?? '') == $tag->id ? 'selected' : '' }}>
                            {{ $tag->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-sm-6 col-lg-3">
                <label for="filter-search" class="form-label form-label-sm mb-1">Search</label>
                <div class="input-group input-group-sm">
                    <input type="search" name="q" id="filter-search"
                           class="form-control filter-control"
                           placeholder="Search issues…"
                           value="{{ $filters['q'] ?? '' }}"
                           maxlength="255">
                    @if ($hasActiveFilter)
                        <a href="{{ route('issues.index') }}"
                           class="btn btn-outline-secondary"
                           title="Clear all filters">
                            &times;
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>
</div>
