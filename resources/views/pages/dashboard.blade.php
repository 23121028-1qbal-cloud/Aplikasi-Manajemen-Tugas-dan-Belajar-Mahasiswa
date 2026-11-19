@extends('layouts.app')

@php
    $nama = $user->mahasiswa?->nama_lengkap ?? $user->guruDosen?->nama_lengkap ?? $user->username;
@endphp

@section('title', 'Dashboard')

@section('content')
    <div class="app-card p-4">
        <p class="small text-body-secondary mb-1">{{ $user->role->label() }}</p>
        <h2 class="h4 mb-2">Selamat datang, {{ $nama }}</h2>
        <p class="text-body-secondary mb-0">
            Ringkasan data yang dapat Anda akses sesuai role dan kepemilikan resource.
        </p>
    </div>

    @if ($summary !== [])
        <div class="row g-3 mt-1">
            @foreach ($summary as $item)
                <div class="col-12 col-sm-6 col-xl-3">
                    <div class="app-card h-100 p-4">
                        <p class="small text-body-secondary mb-1">{{ $item['label'] }}</p>
                        <p class="display-6 fw-semibold mb-1">{{ $item['value'] }}</p>
                        <p class="small text-body-secondary mb-0">{{ $item['hint'] }}</p>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-7">
            <div class="app-card h-100 p-4">
                <h3 class="h6">{{ $itemsTitle }}</h3>

                @forelse ($items as $item)
                    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 border-bottom py-2">
                        <div class="min-w-0">
                            <div class="fw-semibold text-truncate">{{ $item->judul_tugas }}</div>
                            <div class="small text-body-secondary">
                                {{ $item->mataKuliah?->kode_mata_kuliah ?? 'Tanpa mata kuliah' }}
                                &middot; deadline {{ $item->deadline?->format('d M Y H:i') }}
                            </div>
                        </div>
                        <div class="text-nowrap">
                            @if ($item->task_type?->value === 'personal')
                                <span class="badge text-bg-light border">{{ $item->statusTugas?->nama_status ?? '—' }}</span>
                                <a href="{{ route('mahasiswa.tasks.personal.show', $item) }}" class="btn btn-sm btn-outline-secondary ms-1">Detail</a>
                            @elseif ($user->hasRole('guru_dosen'))
                                <span class="badge {{ $item->is_closed ? 'text-bg-secondary' : 'text-bg-success' }}">{{ $item->is_closed ? 'Ditutup' : 'Terbuka' }}</span>
                                <a href="{{ route('lecturer.academic-tasks.show', $item) }}" class="btn btn-sm btn-outline-secondary ms-1">Detail</a>
                            @else
                                <span class="badge {{ $item->is_closed ? 'text-bg-secondary' : 'text-bg-success' }}">{{ $item->is_closed ? 'Ditutup' : 'Terbuka' }}</span>
                                <a href="{{ route('admin.academic-monitoring.tasks.show', $item) }}" class="btn btn-sm btn-outline-secondary ms-1">Detail</a>
                            @endif
                        </div>
                    </div>
                @empty
                    <p class="small text-body-secondary mb-0">Belum ada data untuk ditampilkan.</p>
                @endforelse
            </div>
        </div>

        <div class="col-12 col-lg-5">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Akun</h3>
                <p class="small text-body-secondary">
                    Username <strong>{{ $user->username }}</strong> &middot; status
                    <strong>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</strong>
                </p>
                <a href="{{ route('account.show') }}" class="btn btn-sm btn-outline-primary">Lihat Profil</a>
                <a href="{{ route('account.password.edit') }}" class="btn btn-sm btn-outline-secondary">Ubah Password</a>
                <a href="{{ route('account.appearance.edit') }}" class="btn btn-sm btn-outline-secondary">Pengaturan Tampilan</a>
            </div>
        </div>
    </div>
@endsection
