@php($theme = request()->cookie('theme') === 'dark' ? 'dark' : 'light')
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" data-theme="{{ $theme }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Aplikasi') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('head')
</head>
<body class="app-guest">
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

    <main id="main-content" class="container" tabindex="-1">
        <div class="d-flex justify-content-end mb-3">
            <button type="button" class="app-icon-button" data-theme-toggle aria-pressed="false" aria-label="Aktifkan mode gelap" title="Mode gelap">
                <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
            </button>
        </div>

        @include('layouts.partials.validation-errors')

        @if (session('status'))
            <div class="alert alert-success" role="status">{{ session('status') }}</div>
        @endif

        @yield('content')
    </main>
</body>
</html>
