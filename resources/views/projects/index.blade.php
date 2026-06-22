<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h1 class="h4 mb-0">Projects</h1>
            <a href="{{ route('projects.create') }}" class="btn btn-primary btn-sm">
                + New Project
            </a>
        </div>
    </x-slot>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th>Name</th>
                        <th>Owner</th>
                        <th class="text-center">Issues</th>
                        <th class="text-nowrap">Start Date</th>
                        <th class="text-nowrap">Deadline</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($projects as $project)
                        <tr>
                            <td>
                                <a href="{{ route('projects.show', $project) }}"
                                   class="text-decoration-none fw-medium text-body">
                                    {{ $project->name }}
                                </a>
                                @if ($project->description)
                                    <p class="text-muted small mb-0 mt-1">
                                        {{ Str::limit($project->description, 80) }}
                                    </p>
                                @endif
                            </td>
                            <td class="text-nowrap text-muted small">{{ $project->owner->name }}</td>
                            <td class="text-center">
                                <span class="badge text-bg-secondary">{{ $project->issues_count }}</span>
                            </td>
                            <td class="text-muted small text-nowrap">
                                {{ $project->start_date?->format('M j, Y') ?? '—' }}
                            </td>
                            <td class="text-muted small text-nowrap">
                                {{ $project->deadline?->format('M j, Y') ?? '—' }}
                            </td>
                            <td class="text-end text-nowrap">
                                @can('update', $project)
                                    <a href="{{ route('projects.edit', $project) }}"
                                       class="btn btn-outline-secondary btn-sm">Edit</a>
                                @endcan

                                @can('delete', $project)
                                    <form method="POST"
                                          action="{{ route('projects.destroy', $project) }}"
                                          class="d-inline"
                                          onsubmit="return confirm('Delete project &quot;{{ addslashes($project->name) }}&quot; and all its issues?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-outline-danger btn-sm">Delete</button>
                                    </form>
                                @endcan
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-5">
                                No projects yet.
                                <a href="{{ route('projects.create') }}">Create the first one.</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($projects->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $projects->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
