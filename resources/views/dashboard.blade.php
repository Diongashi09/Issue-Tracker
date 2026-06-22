<x-app-layout>
    <x-slot name="header">
        <h2 class="h5 mb-0">{{ __('Dashboard') }}</h2>
    </x-slot>

    <div class="card shadow-sm">
        <div class="card-body p-4">
            {{ __("You're logged in!") }}
        </div>
    </div>
</x-app-layout>
