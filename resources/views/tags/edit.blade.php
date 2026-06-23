<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('tags.index') }}" class="text-decoration-none text-muted">&larr;</a>
            <h1 class="h4 mb-0">Edit Tag</h1>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-5">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('tags.update', $tag) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <x-input-label for="tag_name" :value="__('Name')" />
                            <x-text-input id="tag_name" name="name" type="text"
                                          :value="old('name', $tag->name)" required autofocus />
                            <x-input-error :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-4">
                            <x-input-label for="tag_color" :value="__('Colour')" />
                            <div class="d-flex align-items-center gap-2">
                                <input type="color" id="tag_color" name="color"
                                       class="form-control form-control-color @error('color') is-invalid @enderror"
                                       value="{{ old('color', $tag->color ?? '#6c757d') }}"
                                       title="Pick a badge colour">
                                <span class="text-muted small">
                                    Preview: <x-tag-badge :tag="$tag" />
                                </span>
                            </div>
                            <x-input-error :messages="$errors->get('color')" />
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('tags.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <x-primary-button>Save Changes</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
