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
        <div class="col-12 col-lg-4">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Ubah Nama</h3>
                <p class="small text-body-secondary">
                    Hanya nama lengkap yang dapat diubah. NPM dan username bersifat tetap.
                </p>

                <form method="POST" action="{{ route('admin.students.update', $student) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text"
                               id="nama_lengkap"
                               name="nama_lengkap"
                               value="{{ old('nama_lengkap', $student->nama_lengkap) }}"
                               class="form-control @error('nama_lengkap') is-invalid @enderror"
                               required>
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary">Simpan Nama</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Status Akun</h3>
                <p class="small text-body-secondary">
                    Akun nonaktif tidak dapat membuat sesi baru dan sesi aktifnya diakhiri pada request berikutnya.
                </p>

                <button type="button"
                        class="btn btn-sm {{ $student->user?->is_active ? 'btn-outline-danger' : 'btn-outline-success' }}"
                        data-bs-toggle="modal"
                        data-bs-target="#student-status-{{ $student->id_mahasiswa }}">
                    {{ $student->user?->is_active ? 'Nonaktifkan Akun' : 'Aktifkan Akun' }}
                </button>

                <x-confirm-modal
                    id="student-status-{{ $student->id_mahasiswa }}"
                    :title="$student->user?->is_active ? 'Nonaktifkan akun ini?' : 'Aktifkan akun ini?'"
                    :message="$student->user?->is_active
                        ? 'Mahasiswa tidak dapat login sampai akun diaktifkan kembali. Riwayat data tetap tersimpan.'
                        : 'Mahasiswa dapat login kembali setelah akun diaktifkan.'"
                    :action="route('admin.students.status', $student)"
                    method="PATCH"
                    :confirm="$student->user?->is_active ? 'Nonaktifkan' : 'Aktifkan'"
                    :variant="$student->user?->is_active ? 'danger' : 'success'"
                >
                    <input type="hidden" name="is_active" value="{{ $student->user?->is_active ? '0' : '1' }}">
                </x-confirm-modal>
            </div>
        </div>

        <div class="col-12 col-lg-4">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Temporary Password</h3>
                <p class="small text-body-secondary">
                    Password baru berlaku 72 jam dan wajib diganti pada login pertama. Password ditampilkan satu kali.
                </p>

                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal"
                        data-bs-target="#student-password-{{ $student->id_mahasiswa }}">
                    Terbitkan Temporary Password
                </button>

                <x-confirm-modal
                    id="student-password-{{ $student->id_mahasiswa }}"
                    title="Terbitkan temporary password?"
                    message="Password lama langsung tidak berlaku. Password baru hanya ditampilkan sekali setelah diterbitkan."
                    :action="route('admin.students.temporary-password', $student)"
                    confirm="Terbitkan"
                    variant="primary"
                />
            </div>
        </div>
    </div>
@endsection
