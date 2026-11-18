@php($user = auth()->user())
<header class="app-header d-flex align-items-center gap-2 px-3 px-lg-4 sticky-top">
    <button type="button"
            class="app-icon-button d-lg-none"
            data-bs-toggle="offcanvas"
            data-bs-target="#appNav"
            aria-controls="appNav"
            aria-label="Buka navigasi">
        <i class="bi bi-list fs-5" aria-hidden="true"></i>
    </button>

    <div class="me-auto min-w-0">
        @hasSection('breadcrumb')
            <nav aria-label="Breadcrumb" class="small text-body-secondary">
                @yield('breadcrumb')
            </nav>
        @endif
        <h1 class="h6 mb-0 text-truncate">@yield('title', 'Dashboard')</h1>
    </div>

    <button type="button" class="app-icon-button" data-theme-toggle aria-pressed="false" aria-label="Aktifkan mode gelap" title="Mode gelap">
        <i class="bi bi-moon-stars" data-theme-icon aria-hidden="true"></i>
    </button>

    @if (Route::has('notifications.index'))
        <a href="{{ route('notifications.index') }}" class="app-icon-button" aria-label="Notifikasi">
            <i class="bi bi-bell" aria-hidden="true"></i>
        </a>
    @endif

    @auth
        <div class="dropdown">
            <button class="btn btn-sm btn-outline-secondary dropdown-toggle d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="dropdown"
                    aria-expanded="false">
                <i class="bi bi-person-circle" aria-hidden="true"></i>
                <span class="d-none d-md-inline">{{ $user->username ?? 'Akun' }}</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end">
                @if (Route::has('account.show'))
                    <li><a class="dropdown-item" href="{{ route('account.show') }}">Profil Akun</a></li>
                @endif
                @if (Route::has('account.appearance.edit'))
                    <li><a class="dropdown-item" href="{{ route('account.appearance.edit') }}">Tampilan</a></li>
                @endif
                @if (Route::has('logout'))
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">Keluar</button>
                        </form>
                    </li>
                @endif
            </ul>
        </div>
    @endauth
</header>
