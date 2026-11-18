@extends('layouts.app')

@section('title', 'Akun Guru/Dosen')

@section('content')
    @if (session('temporary_password'))
        @php($temporary = session('temporary_password'))
        <div class="alert alert-warning" role="alert">
            <p class="fw-semibold mb-1">Temporary password untuk <code>{{ $temporary['username'] }}</code></p>
            <p class="mb-1"><code class="fs-6">{{ $temporary['password'] }}</code></p>
            <p class="small mb-0">
                Tampilkan sekali kepada pengguna melalui kanal aman. Nilai ini tidak disimpan dalam log
                dan tidak dapat ditampilkan ulang.
            </p>
        </div>
    @endif

    <div class="row g-3">
        <div class="col-12 col-xl-5">
            <div class="app-card p-4">
                <h2 class="h6 mb-3">Buat Akun Guru/Dosen</h2>

                <form method="POST" action="{{ route('admin.lecturers.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                        <input type="text"
                               id="nama_lengkap"
                               name="nama_lengkap"
                               value="{{ old('nama_lengkap') }}"
                               class="form-control @error('nama_lengkap') is-invalid @enderror"
                               required>
                        @error('nama_lengkap')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="nip_guru" class="form-label">NIP</label>
                        <input type="text"
                               id="nip_guru"
                               name="nip_guru"
                               value="{{ old('nip_guru') }}"
                               class="form-control @error('nip_guru') is-invalid @enderror"
                               required>
                        @error('nip_guru')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-4">
                        <label for="username" class="form-label">Username</label>
                        <input type="text"
                               id="username"
                               name="username"
                               value="{{ old('username') }}"
                               class="form-control @error('username') is-invalid @enderror"
                               required>
                        @error('username')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <p class="small text-body-secondary">
                        Password awal dibuat otomatis dan ditampilkan satu kali setelah akun tersimpan.
                    </p>

                    <button type="submit" class="btn btn-primary">Buat Akun</button>
                </form>
            </div>
        </div>

        <div class="col-12 col-xl-7">
            <div class="app-card p-4">
                <h2 class="h6 mb-3">Daftar Akun Guru/Dosen</h2>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <caption class="visually-hidden">Daftar akun guru/dosen</caption>
                        <thead>
                            <tr>
                                <th scope="col">Nama</th>
                                <th scope="col">NIP</th>
                                <th scope="col">Username</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($lecturers as $lecturer)
                                <tr>
                                    <td>{{ $lecturer->nama_lengkap }}</td>
                                    <td>{{ $lecturer->nip_guru }}</td>
                                    <td>{{ $lecturer->user?->username }}</td>
                                    <td>
                                        @if ($lecturer->user?->is_active)
                                            <span class="badge text-bg-success">Aktif</span>
                                        @else
                                            <span class="badge text-bg-secondary">Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <form method="POST" action="{{ route('admin.lecturers.temporary-password', $lecturer) }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                                Terbitkan Temporary Password
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-body-secondary">Belum ada akun guru/dosen.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($lecturers->hasPages())
                    <div class="mt-3">
                        {{ $lecturers->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
