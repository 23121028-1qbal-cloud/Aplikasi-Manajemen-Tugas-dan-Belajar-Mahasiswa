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
<body>
    <a class="skip-link" href="#main-content">Lewati ke konten utama</a>

    <div class="app-shell d-lg-flex">
        <aside class="app-sidebar d-none d-lg-flex flex-column flex-shrink-0">
            <nav class="d-flex flex-column flex-grow-1 p-3 overflow-y-auto" aria-label="Navigasi utama">
                @include('layouts.partials.sidebar-nav')
            </nav>
        </aside>

        <div class="offcanvas offcanvas-start app-sidebar" tabindex="-1" id="appNav" aria-labelledby="appNavLabel">
            <div class="offcanvas-header">
                <h2 class="offcanvas-title h6 mb-0" id="appNavLabel">Navigasi</h2>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Tutup navigasi"></button>
            </div>
            <div class="offcanvas-body d-flex flex-column">
                <nav class="d-flex flex-column flex-grow-1" aria-label="Navigasi utama">
                    @include('layouts.partials.sidebar-nav')
                </nav>
            </div>
        </div>

        <div class="app-main d-flex flex-column flex-grow-1">
            @include('layouts.partials.header')

            <main id="main-content" class="app-content flex-grow-1" tabindex="-1">
                @if (session('status'))
                    <div class="alert alert-success" role="status">{{ session('status') }}</div>
                @endif

                @include('layouts.partials.validation-errors')

                @yield('content')
            </main>

            @include('layouts.partials.footer')
        </div>
    </div>
</body>
</html>
