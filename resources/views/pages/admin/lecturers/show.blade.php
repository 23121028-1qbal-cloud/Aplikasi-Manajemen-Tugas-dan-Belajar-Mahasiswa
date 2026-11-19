@extends('layouts.app')

@section('title', 'Detail Akun Guru/Dosen')

@section('content')
    @include('layouts.partials.temporary-password-alert')

    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">Akun Guru/Dosen</p>
                <h2 class="h5 mb-0">{{ $lecturer->nama_lengkap }}</h2>
            </div>
            <a href="{{ route('admin.lecturers.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">NIP</dt>
            <dd class="col-sm-9">{{ $lecturer->nip_guru }}</dd>

            <dt class="col-sm-3">Username</dt>
            <dd class="col-sm-9">{{ $lecturer->user?->username }}</dd>

            <dt class="col-sm-3">Status Akun</dt>
            <dd class="col-sm-9">
                @if ($lecturer->user?->is_active)
                    <span class="badge text-bg-success">Aktif</span>
                @else
                    <span class="badge text-bg-secondary">Nonaktif</span>
                @endif
            </dd>

            <dt class="col-sm-3">Mata Kuliah</dt>
            <dd class="col-sm-9">{{ $lecturer->mata_kuliah_count }}</dd>

            <dt class="col-sm-3">Status Arsip</dt>
            <dd class="col-sm-9">
                @if ($lecturer->is_archived)
                    <span class="badge text-bg-dark">Diarsipkan</span>
                    <span class="small text-body-secondary ms-1">{{ $lecturer->tanggal_arsip?->format('d M Y H:i') }}</span>
                @else
                    <span class="badge text-bg-light border">Tidak diarsipkan</span>
                @endif
            </dd>
        </dl>
    </div>

    <div class="row g-3 mt-1">
        <div class="col-12 col-lg-6">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Ubah Nama</h3>
                <p class="small text-body-secondary">NIP dan username bersifat tetap.</p>

                <form method="POST" action="{{ route('admin.lecturers.update', $lecturer) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text"
                               id="nama_lengkap"
                               name="nama_lengkap"
                               value="{{ old('nama_lengkap', $lecturer->nama_lengkap) }}"
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

        <div class="col-12 col-lg-6">
            <div class="app-card h-100 p-4">
                <h3 class="h6">Temporary Password</h3>
                <p class="small text-body-secondary">
                    Password baru berlaku 72 jam dan wajib diganti pada login pertama.
                </p>

                <button type="button"
                        class="btn btn-sm btn-outline-secondary"
                        data-bs-toggle="modal"
                        data-bs-target="#lecturer-password-{{ $lecturer->id_guru_dosen }}">
                    Terbitkan Temporary Password
                </button>

                <x-confirm-modal
                    id="lecturer-password-{{ $lecturer->id_guru_dosen }}"
                    title="Terbitkan temporary password?"
                    message="Password lama langsung tidak berlaku. Password baru hanya ditampilkan sekali setelah diterbitkan."
                    :action="route('admin.lecturers.temporary-password', $lecturer)"
                    confirm="Terbitkan"
                    variant="primary"
                />
            </div>
        </div>
    </div>

    <div class="app-card p-4 mt-3">
        <h3 class="h6">Arsip Akun</h3>

        @if ($lecturer->is_archived)
            <p class="small text-body-secondary">
                Akun ini diarsipkan dan tidak dapat login. Pulihkan untuk mengaktifkannya kembali. Mata kuliah yang sudah dialihkan tidak kembali secara otomatis.
            </p>

            <button type="button"
                    class="btn btn-sm btn-outline-success"
                    data-bs-toggle="modal"
                    data-bs-target="#lecturer-restore-{{ $lecturer->id_guru_dosen }}">
                Pulihkan Akun
            </button>

            <x-confirm-modal
                id="lecturer-restore-{{ $lecturer->id_guru_dosen }}"
                title="Pulihkan akun ini?"
                message="Akun akan aktif kembali. Mata kuliah yang sudah dialihkan tetap berada pada dosen pengampu saat ini."
                :action="route('admin.lecturers.restore', $lecturer)"
                method="PATCH"
                confirm="Pulihkan"
                variant="success"
            />
        @else
            <p class="small text-body-secondary">
                Arsip menonaktifkan akun dan mempertahankan seluruh riwayat. Dosen hanya dapat diarsipkan jika tidak memiliki mata kuliah non-arsip.
            </p>

            @if ($lecturer->active_courses_count > 0)
                <div class="alert alert-warning mb-0" role="status">
                    Masih ada {{ $lecturer->active_courses_count }} mata kuliah non-arsip. Pindahkan ke dosen lain atau arsipkan mata kuliah tersebut terlebih dahulu.
                </div>
            @else
                <button type="button"
                        class="btn btn-sm btn-outline-dark"
                        data-bs-toggle="modal"
                        data-bs-target="#lecturer-archive-{{ $lecturer->id_guru_dosen }}">
                    Arsipkan Akun
                </button>

                <x-confirm-modal
                    id="lecturer-archive-{{ $lecturer->id_guru_dosen }}"
                    title="Arsipkan akun ini?"
                    message="Akun tidak dapat login sampai dipulihkan. Tugas akademik dan progres yang sudah ada tetap tersimpan."
                    :action="route('admin.lecturers.archive', $lecturer)"
                    method="PATCH"
                    confirm="Arsipkan"
                    variant="danger"
                />
            @endif
        @endif
    </div>
@endsection
