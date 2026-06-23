<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h1 class="h4 mb-0">Issues</h1>
            <a href="{{ route('issues.create') }}" class="btn btn-primary btn-sm">
                + New Issue
            </a>
        </div>
    </x-slot>

    {{-- Phase 4 adds the filters bar (status/priority/tag + search) above this card. --}}
    <div class="card shadow-sm">
        @include('issues.partials.issue-list', ['issues' => $issues, 'showProject' => true])

        @if ($issues->hasPages())
            <div class="card-footer d-flex justify-content-center">
                {{ $issues->links() }}
            </div>
        @endif
    </div>

</x-app-layout>
