<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('projects.index') }}" class="text-decoration-none text-muted">&larr;</a>
                <h1 class="h4 mb-0">{{ $project->name }}</h1>
            </div>
            @canany(['update', 'delete'], $project)
            <div class="d-flex gap-2">
                @can('update', $project)
                    <a href="{{ route('projects.edit', $project) }}" class="btn btn-outline-secondary btn-sm">
                        Edit Project
                    </a>
                @endcan

                @can('delete', $project)
                    <form method="POST" action="{{ route('projects.destroy', $project) }}" class="d-inline"
                          onsubmit="return confirm('Delete project and all its issues?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                    </form>
                @endcan
            </div>
            @endcanany
        </div>
    </x-slot>

    {{-- Project meta card --}}
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3">
                @if ($project->description)
                    <div class="col-12">
                        <p class="mb-0 text-muted">{{ $project->description }}</p>
                    </div>
                @endif
                <div class="col-sm-4 col-md-3">
                    <p class="text-muted small mb-1">Owner</p>
                    <p class="mb-0 fw-medium">{{ $project->owner->name }}</p>
                </div>
                <div class="col-sm-4 col-md-3">
                    <p class="text-muted small mb-1">Start Date</p>
                    <p class="mb-0">{{ $project->start_date?->format('M j, Y') ?? '—' }}</p>
                </div>
                <div class="col-sm-4 col-md-3">
                    <p class="text-muted small mb-1">Deadline</p>
                    <p class="mb-0 {{ $project->deadline?->isPast() ? 'text-danger' : '' }}">
                        {{ $project->deadline?->format('M j, Y') ?? '—' }}
                    </p>
                </div>
                <div class="col-sm-4 col-md-3">
                    <p class="text-muted small mb-1">Issues</p>
                    <p class="mb-0">{{ $issues->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Issues section --}}
    <div class="card shadow-sm">
        <div class="card-header d-flex align-items-center justify-content-between">
            <h2 class="h6 mb-0">Issues</h2>
            @if (Route::has('issues.create'))
                <a href="{{ route('issues.create', ['project' => $project]) }}"
                   class="btn btn-primary btn-sm">+ New Issue</a>
            @endif
        </div>

        @include('issues.partials.issue-list', ['issues' => $issues])

        @if ($issues->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $issues->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
