@php
    $role = auth()->user()?->role;

    $navigation = [
        'mahasiswa' => [
            ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'mahasiswa.dashboard'],
            ['label' => 'Semua Tugas', 'icon' => 'bi-list-task', 'route' => 'mahasiswa.tasks.index'],
            ['label' => 'Riwayat Tugas', 'icon' => 'bi-archive', 'route' => 'mahasiswa.task-history'],
            ['label' => 'Aktivitas Belajar', 'icon' => 'bi-journal-bookmark', 'route' => 'mahasiswa.study-activities.index'],
            ['label' => 'Mata Kuliah', 'icon' => 'bi-mortarboard', 'route' => 'mahasiswa.courses.index'],
        ],
        'guru_dosen' => [
            ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'lecturer.dashboard'],
            ['label' => 'Mata Kuliah Saya', 'icon' => 'bi-mortarboard', 'route' => 'lecturer.courses.index'],
            ['label' => 'Tugas Akademik', 'icon' => 'bi-clipboard-check', 'route' => 'lecturer.academic-tasks.index'],
        ],
        'admin' => [
            ['label' => 'Dashboard', 'icon' => 'bi-speedometer2', 'route' => 'admin.dashboard'],
            ['label' => 'Akun Mahasiswa', 'icon' => 'bi-people', 'route' => 'admin.students.index'],
            ['label' => 'Akun Guru/Dosen', 'icon' => 'bi-person-badge', 'route' => 'admin.lecturers.index'],
            ['label' => 'Mata Kuliah', 'icon' => 'bi-mortarboard', 'route' => 'admin.courses.index'],
            ['label' => 'Enrollment', 'icon' => 'bi-person-check', 'route' => 'admin.enrollments.index'],
            ['label' => 'Monitoring Akademik', 'icon' => 'bi-graph-up', 'route' => 'admin.academic-monitoring.index'],
        ],
    ];

    $items = $navigation[$role] ?? [];
    $accountItems = [
        ['label' => 'Notifikasi', 'icon' => 'bi-bell', 'route' => 'notifications.index'],
        ['label' => 'Pengaturan Akun', 'icon' => 'bi-person-gear', 'route' => 'account.show'],
        ['label' => 'Tampilan', 'icon' => 'bi-palette', 'route' => 'account.appearance.edit'],
    ];
@endphp

<a href="{{ url('/') }}" class="app-brand d-flex align-items-center gap-2 px-2 py-2 mb-2">
    <i class="bi bi-journal-check fs-4" aria-hidden="true"></i>
    <span class="lh-sm">Manajemen Tugas<br><small class="fw-normal text-body-secondary">Mahasiswa</small></span>
</a>

<p class="nav-heading px-2 mt-3 mb-2">Menu</p>
<ul class="nav nav-pills flex-column gap-1">
    @forelse ($items as $item)
        @php
            $href = Route::has($item['route']) ? route($item['route']) : null;
            $active = $href !== null && request()->is(trim(parse_url($href, PHP_URL_PATH) ?? '', '/') . '*');
        @endphp
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 {{ $active ? 'active' : '' }}"
               href="{{ $href ?? '#' }}"
               @if ($href === null) aria-disabled="true" tabindex="-1" @endif
               @if ($active) aria-current="page" @endif>
                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        </li>
    @empty
        <li class="nav-item">
            <span class="nav-link disabled">Menu belum tersedia.</span>
        </li>
    @endforelse
</ul>

<hr class="my-3">

<p class="nav-heading px-2 mb-2">Akun</p>
<ul class="nav nav-pills flex-column gap-1">
    @foreach ($accountItems as $item)
        @php
            $href = Route::has($item['route']) ? route($item['route']) : null;
            $active = $href !== null && request()->is(trim(parse_url($href, PHP_URL_PATH) ?? '', '/') . '*');
        @endphp
        <li class="nav-item">
            <a class="nav-link d-flex align-items-center gap-2 {{ $active ? 'active' : '' }}"
               href="{{ $href ?? '#' }}"
               @if ($href === null) aria-disabled="true" tabindex="-1" @endif
               @if ($active) aria-current="page" @endif>
                <i class="bi {{ $item['icon'] }}" aria-hidden="true"></i>
                <span>{{ $item['label'] }}</span>
            </a>
        </li>
    @endforeach
</ul>

<div class="mt-auto pt-4">
    @if (Route::has('logout'))
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="btn btn-outline-secondary w-100 d-flex align-items-center justify-content-center gap-2">
                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>
                <span>Keluar</span>
            </button>
        </form>
    @endif
</div>
