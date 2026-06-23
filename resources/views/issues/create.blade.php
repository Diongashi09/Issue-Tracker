<x-app-layout>
    <x-slot name="header">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('issues.index') }}" class="text-decoration-none text-muted">&larr;</a>
            <h1 class="h4 mb-0">New Issue</h1>
        </div>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-body p-4">
                    <form method="POST" action="{{ route('issues.store') }}">
                        @csrf

                        <div class="mb-3">
                            <x-input-label for="project_id" :value="__('Project')" />
                            <select id="project_id" name="project_id"
                                    class="form-select @error('project_id') is-invalid @enderror" required>
                                <option value="">— Select a project —</option>
                                @foreach ($projects as $project)
                                    <option value="{{ $project->id }}"
                                        @selected((int) old('project_id', $selectedProjectId) === $project->id)>
                                        {{ $project->name }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('project_id')" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="title" :value="__('Title')" />
                            <x-text-input id="title" name="title" type="text"
                                          :value="old('title')" required autofocus />
                            <x-input-error :messages="$errors->get('title')" />
                        </div>

                        <div class="mb-3">
                            <x-input-label for="description" :value="__('Description')" />
                            <textarea id="description" name="description"
                                      class="form-control @error('description') is-invalid @enderror"
                                      rows="5">{{ old('description') }}</textarea>
                            <x-input-error :messages="$errors->get('description')" />
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-sm-4">
                                <x-input-label for="status" :value="__('Status')" />
                                <select id="status" name="status"
                                        class="form-select @error('status') is-invalid @enderror" required>
                                    @foreach (\App\Enums\IssueStatus::cases() as $case)
                                        <option value="{{ $case->value }}"
                                            @selected(old('status', \App\Enums\IssueStatus::Open->value) === $case->value)>
                                            {{ $case->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('status')" />
                            </div>
                            <div class="col-sm-4">
                                <x-input-label for="priority" :value="__('Priority')" />
                                <select id="priority" name="priority"
                                        class="form-select @error('priority') is-invalid @enderror" required>
                                    @foreach (\App\Enums\IssuePriority::cases() as $case)
                                        <option value="{{ $case->value }}"
                                            @selected(old('priority', \App\Enums\IssuePriority::Medium->value) === $case->value)>
                                            {{ $case->label() }}
                                        </option>
                                    @endforeach
                                </select>
                                <x-input-error :messages="$errors->get('priority')" />
                            </div>
                            <div class="col-sm-4">
                                <x-input-label for="due_date" :value="__('Due Date')" />
                                <x-text-input id="due_date" name="due_date" type="date"
                                              :value="old('due_date')" />
                                <x-input-error :messages="$errors->get('due_date')" />
                            </div>
                        </div>

                        <div class="mb-3">
                            <x-input-label for="tags" :value="__('Tags')" />
                            <select id="tags" name="tags[]" multiple size="5"
                                    class="form-select @error('tags') is-invalid @enderror @error('tags.*') is-invalid @enderror">
                                @foreach ($tags as $tag)
                                    <option value="{{ $tag->id }}"
                                        @selected(in_array($tag->id, old('tags', []), false))>
                                        {{ $tag->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">Hold Ctrl (Cmd on Mac) to select multiple.</div>
                            <x-input-error :messages="$errors->get('tags')" />
                            <x-input-error :messages="$errors->get('tags.*')" />
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('issues.index') }}" class="btn btn-outline-secondary">
                                Cancel
                            </a>
                            <x-primary-button>Create Issue</x-primary-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

</x-app-layout>
