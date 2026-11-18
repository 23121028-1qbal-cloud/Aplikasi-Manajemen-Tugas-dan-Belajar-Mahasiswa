@extends('layouts.guest')

@section('title', 'Fondasi Aplikasi')

@section('content')
    <div class="app-guest-card mx-auto">
        <div class="app-card p-4 p-md-5">
            <div class="d-flex align-items-center gap-2 mb-3">
                <i class="bi bi-journal-check fs-3 text-primary" aria-hidden="true"></i>
                <h1 class="h4 mb-0">{{ config('app.name') }}</h1>
            </div>

            <p class="text-body-secondary">
                Fondasi aplikasi (Phase 2) sudah berjalan. Halaman ini sementara dan akan digantikan
                oleh halaman login pada Phase 3.
            </p>

            <ul class="list-unstyled small mb-4">
                <li class="d-flex gap-2 py-1">
                    <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                    <span>Laravel {{ app()->version() }} dengan Blade</span>
                </li>
                <li class="d-flex gap-2 py-1">
                    <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                    <span>Bootstrap 5 dan Bootstrap Icons melalui Vite</span>
                </li>
                <li class="d-flex gap-2 py-1">
                    <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                    <span>Koneksi database MySQL/MariaDB <code>db_manajemen_akademik</code></span>
                </li>
                <li class="d-flex gap-2 py-1">
                    <i class="bi bi-check-circle-fill text-success" aria-hidden="true"></i>
                    <span>Mode terang dan gelap</span>
                </li>
            </ul>

            <p class="small text-body-secondary mb-0">
                Autentikasi, master data, dan modul tugas belum diimplementasikan karena keputusan
                schema masih menunggu persetujuan (CP2).
            </p>
        </div>
    </div>
@endsection
