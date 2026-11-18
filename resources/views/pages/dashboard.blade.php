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
            Ringkasan tugas, progres, dan aktivitas akan tersedia pada fase modul terkait.
            Untuk saat ini, kelola keamanan akun melalui pengaturan akun.
        </p>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Akun</h3>
                <p class="small text-body-secondary">
                    Username <strong>{{ $user->username }}</strong> &middot; status
                    <strong>{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</strong>
                </p>
                <a href="{{ route('account.show') }}" class="btn btn-sm btn-outline-primary">Lihat Profil</a>
                <a href="{{ route('account.password.edit') }}" class="btn btn-sm btn-outline-secondary">Ubah Password</a>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Modul berikutnya</h3>
                <p class="small text-body-secondary mb-0">
                    @if ($user->hasRole('mahasiswa'))
                        Kelola tugas personal dan aktivitas belajar, serta perbarui progres tugas akademik melalui menu Tugas.
                    @elseif ($user->hasRole('guru_dosen'))
                        Mata kuliah yang diampu dan tugas akademik tersedia pada menu Mata Kuliah Saya dan Tugas Akademik.
                    @else
                        Akun, mata kuliah, enrollment, dan monitoring akademik read-only tersedia pada menu administrasi.
                    @endif
                </p>
            </div>
        </div>
    </div>
@endsection
