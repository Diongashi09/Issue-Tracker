<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name', 'Laravel') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-light">

    <div class="min-vh-100 d-flex align-items-center justify-content-center py-4">
        <div class="w-100" style="max-width: 440px;">

            <div class="text-center mb-4">
                <a class="text-decoration-none fw-bold fs-4 text-dark" href="/">
                    {{ config('app.name', 'Laravel') }}
                </a>
            </div>

            <div class="card shadow-sm">
                <div class="card-body p-4">
                    {{ $slot }}
                </div>
            </div>

        </div>
    </div>

</body>
</html>
