<form method="post" action="{{ route('profile.update') }}">
    @csrf
    @method('patch')

    <div class="mb-3">
        <x-input-label for="name" :value="__('Name')" />
        <x-text-input id="name" name="name" type="text"
                      :value="old('name', $user->name)"
                      required autofocus autocomplete="name" />
        <x-input-error :messages="$errors->get('name')" />
    </div>

    <div class="mb-3">
        <x-input-label for="email" :value="__('Email')" />
        <x-text-input id="email" name="email" type="email"
                      :value="old('email', $user->email)"
                      required autocomplete="username" />
        <x-input-error :messages="$errors->get('email')" />

        @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
            <div class="mt-2">
                <p class="text-muted small mb-1">{{ __('Your email address is unverified.') }}</p>

                <form id="send-verification" method="post" action="{{ route('verification.send') }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-link btn-sm p-0">
                        {{ __('Re-send verification email') }}
                    </button>
                </form>

                @if (session('status') === 'verification-link-sent')
                    <p class="text-success small mt-1">
                        {{ __('A new verification link has been sent to your email address.') }}
                    </p>
                @endif
            </div>
        @endif
    </div>

    <div class="d-flex align-items-center gap-3">
        <x-primary-button>{{ __('Save') }}</x-primary-button>

        @if (session('status') === 'profile-updated')
            <span class="text-success small">Saved.</span>
        @endif
    </div>
</form>
