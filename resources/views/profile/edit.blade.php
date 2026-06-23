<x-app-layout>
    <x-slot name="header">
        <h1 class="h4 mb-0">Profile</h1>
    </x-slot>

    <div class="row justify-content-center">
        <div class="col-lg-8">

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h6 mb-0">Profile Information</h2>
                </div>
                <div class="card-body p-4">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            <div class="card shadow-sm mb-4">
                <div class="card-header">
                    <h2 class="h6 mb-0">Update Password</h2>
                </div>
                <div class="card-body p-4">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            <div class="card shadow-sm border-danger">
                <div class="card-header text-danger">
                    <h2 class="h6 mb-0">Delete Account</h2>
                </div>
                <div class="card-body p-4">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>
    </div>

</x-app-layout>
