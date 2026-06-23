<p class="text-muted small mb-3">
    Once your account is deleted, all of its data will be permanently removed. If you own
    projects, you must delete them first before your account can be deleted.
</p>

<button type="button" class="btn btn-danger btn-sm"
        data-bs-toggle="modal" data-bs-target="#confirmDeletionModal">
    Delete Account
</button>

<div class="modal fade" id="confirmDeletionModal" tabindex="-1"
     aria-labelledby="confirmDeletionLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="{{ route('profile.destroy') }}">
                @csrf
                @method('DELETE')

                <div class="modal-header">
                    <h5 class="modal-title" id="confirmDeletionLabel">Delete Account</h5>
                    <button type="button" class="btn-close"
                            data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body">
                    <p class="text-muted small">
                        Are you sure you want to delete your account? This action cannot be undone.
                    </p>
                    <div class="mb-0">
                        <x-input-label for="del_password" :value="__('Confirm your password')" />
                        <x-text-input id="del_password" name="password" type="password"
                                      placeholder="{{ __('Password') }}"
                                      autocomplete="current-password" />
                        <x-input-error :messages="$errors->userDeletion->get('password')" />
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary"
                            data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger">Delete Account</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Re-open modal automatically when the server returns validation errors --}}
@if ($errors->userDeletion->isNotEmpty())
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            new bootstrap.Modal(document.getElementById('confirmDeletionModal')).show();
        });
    </script>
    @endpush
@endif
