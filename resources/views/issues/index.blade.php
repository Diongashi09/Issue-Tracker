<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between">
            <h1 class="h4 mb-0">Issues</h1>
            <a href="{{ route('issues.create') }}" class="btn btn-primary btn-sm">+ New Issue</a>
        </div>
    </x-slot>

    {{-- Filter bar: status / priority / tag / search (AJAX, debounced) --}}
    @include('issues.partials.filters', ['filters' => $filters, 'allTags' => $allTags])

    <div class="card shadow-sm">
        {{-- AJAX swaps only this div's innerHTML on filter / page changes --}}
        <div id="issue-list-container">
            @include('issues.partials.issue-list', ['issues' => $issues, 'showProject' => true])
        </div>

        {{-- Pagination — also swapped by AJAX; d-none when no pages --}}
        <div id="issue-pagination"
             class="card-footer d-flex justify-content-center{{ $issues->hasPages() ? '' : ' d-none' }}">
            {{ $issues->links() }}
        </div>
    </div>

</x-app-layout>
