<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('projects.index') }}" class="text-decoration-none text-muted">&larr;</a>
            <h1 class="h4 mb-0">New Project</h1>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('projects.store') }}">
                        @csrf

                        <div class="mb-3">
                            <x-input-label for="name" :value="__('Name')" />
                            <x-text-input id="name" name="name" type="text"
                                          :value="old('name')" required autofocus />
                            <x-input-error :messages="$errors->get('name')" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="4">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" />
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-6">
                                <x-input-label for="start_date" :value="__('Start Date')" />
                                <x-text-input id="start_date" name="start_date" type="date"
                                              :value="old('start_date')" />
                                <x-input-error :messages="$errors->get('start_date')" />
                            </div>
                            <div class="col-sm-6">
                                <x-input-label for="deadline" :value="__('Deadline')" />
                                <x-text-input id="deadline" name="deadline" type="date"
                                              :value="old('deadline')" />
                                <x-input-error :messages="$errors->get('deadline')" />
                            </div>
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('projects.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <x-primary-button>Create Project</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
