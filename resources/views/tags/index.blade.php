<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Tags</h1>
    </x-slot>

    <div class="row g-4">

        {{-- Create new tag --}}
        <div class="col-lg-4">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h2 class="h6 mb-0">New Tag</h2>
                </div>
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tags.store') }}">
                        @csrf

                        <div class="mb-3">
                            <x-input-label for="tag_name" :value="__('Name')" />
                            <x-text-input id="tag_name" name="name" type="text"
                                          :value="old('name')" required autofocus
                                          placeholder="e.g. bug, feature…" />
                            <x-input-error :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="tag_color" :value="__('Colour')" />
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="tag_color" name="color"
                                       class="form-control form-control-color @error('color') is-invalid @enderror"
                                       value="{{ old('color', '#6c757d') }}"
                                       title="Pick a badge colour">
                                <span class="text-muted small">Leave default for grey.</span>
                            </div>
                            <x-input-error :messages="$errors->get('color')" />
                        </div>

                        <x-primary-button>Create Tag</x-primary-button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Tag library --}}
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header d-flex align-items-center justify-content-between">
                    <h2 class="h6 mb-0">Tag Library</h2>
                    <span class="badge text-bg-secondary">{{ $tags->count() }}</span>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Tag</th>
                                <th class="text-center">Issues</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($tags as $tag)
                                <tr>
                                    <td>
                                        <x-tag-badge :tag="$tag" />
                                    </td>
                                    <td class="text-center">
                                        <a href="{{ route('issues.index', ['tag' => $tag->id]) }}"
                                           class="text-decoration-none text-muted small">
                                            {{ $tag->issues_count }}
                                            {{ Str::plural('issue', $tag->issues_count) }}
                                        </a>
                                    </td>
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('tags.edit', $tag) }}"
                                           class="btn btn-outline-secondary btn-sm">Edit</a>

                                        @php
                                            $usageNote = $tag->issues_count > 0
                                                ? " It is used on {$tag->issues_count} " . Str::plural('issue', $tag->issues_count) . '.'
                                                : '';
                                        @endphp
                                        <form method="POST"
                                              action="{{ route('tags.destroy', $tag) }}"
                                              class="d-inline"
                                              onsubmit="return confirm('Delete tag {{ addslashes($tag->name) }}?{{ addslashes($usageNote) }}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="btn btn-outline-danger btn-sm">Delete</button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="text-center text-muted py-5 fst-italic">
                                        No tags yet. Create the first one.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>

</x-app-layout>
