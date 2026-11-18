@extends('layouts.app')

@section('title', 'Detail Akun Mahasiswa')

@section('content')
    @include('layouts.partials.temporary-password-alert')

    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">Akun Mahasiswa</p>
                <h2 class="h5 mb-0">{{ $student->nama_lengkap }}</h2>
            </div>
            <a href="{{ route('admin.students.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">NPM</dt>
            <dd class="col-sm-9">{{ $student->npm }}</dd>

            <dt class="col-sm-3">Username</dt>
            <dd class="col-sm-9">{{ $student->user?->username }}</dd>

            <dt class="col-sm-3">Status Akun</dt>
            <dd class="col-sm-9">
                @if ($student->user?->is_active)
                    <span class="badge text-bg-success">Aktif</span>
                @else
                    <span class="badge text-bg-secondary">Nonaktif</span>
                @endif
            </dd>

            <dt class="col-sm-3">Terdaftar Sejak</dt>
            <dd class="col-sm-9">{{ $student->created_at?->format('d M Y') }}</dd>
        </dl>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-md-6">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Status Akun</h3>
                <p class="small text-body-secondary">
                    Akun nonaktif tidak dapat membuat sesi baru. Sesi yang sedang aktif akan diakhiri
                    pada request berikutnya.
                </p>

                <form method="POST" action="{{ route('admin.students.status', $student) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="is_active" value="{{ $student->user?->is_active ? '0' : '1' }}">
                    <button type="submit" class="btn btn-sm {{ $student->user?->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}">
                        {{ $student->user?->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                    </button>
                </form>
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Temporary Password</h3>
                <p class="small text-body-secondary">
                    Password baru ditampilkan satu kali setelah diterbitkan. Password lama langsung
                    tidak berlaku.
                </p>

                <form method="POST" action="{{ route('admin.students.temporary-password', $student) }}">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary">Terbitkan Temporary Password</button>
                </form>
            </div>
        </div>
    </div>
@endsection
