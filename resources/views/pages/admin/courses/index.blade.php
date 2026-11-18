@extends('layouts.app')

@section('title', 'Mata Kuliah')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="app-card p-4">
                <h2 class="h6 mb-3">Tambah Mata Kuliah</h2>

                @if ($lecturers->isEmpty())
                    <p class="small text-body-secondary mb-0">
                        Belum ada akun Guru/Dosen. Buat akun terlebih dahulu agar mata kuliah dapat memiliki pengampu.
                    </p>
                @else
                    <form method="POST" action="{{ route('admin.courses.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="kode_mata_kuliah" class="form-label">Kode Mata Kuliah</label>
                            <input type="text"
                                   id="kode_mata_kuliah"
                                   name="kode_mata_kuliah"
                                   value="{{ old('kode_mata_kuliah') }}"
                                   class="form-control @error('kode_mata_kuliah') is-invalid @enderror"
                                   required>
                            @error('kode_mata_kuliah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama_mata_kuliah" class="form-label">Nama Mata Kuliah</label>
                            <input type="text"
                                   id="nama_mata_kuliah"
                                   name="nama_mata_kuliah"
                                   value="{{ old('nama_mata_kuliah') }}"
                                   class="form-control @error('nama_mata_kuliah') is-invalid @enderror"
                                   required>
                            @error('nama_mata_kuliah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="guru_dosen_id" class="form-label">Dosen Pengampu</label>
                            <select id="guru_dosen_id"
                                    name="guru_dosen_id"
                                    class="form-select @error('guru_dosen_id') is-invalid @enderror"
                                    required>
                                <option value="">Pilih dosen</option>
                                @foreach ($lecturers as $lecturer)
                                    <option value="{{ $lecturer->id_guru_dosen }}" @selected(old('guru_dosen_id') == $lecturer->id_guru_dosen)>
                                        {{ $lecturer->nama_lengkap }} ({{ $lecturer->nip_guru }})
                                    </option>
                                @endforeach
                            </select>
                            @error('guru_dosen_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Mata Kuliah</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="app-card p-4">
                <h2 class="h6 mb-3">Daftar Mata Kuliah</h2>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <caption class="visually-hidden">Daftar mata kuliah</caption>
                        <thead>
                            <tr>
                                <th scope="col">Kode</th>
                                <th scope="col">Nama</th>
                                <th scope="col">Dosen</th>
                                <th scope="col">Enrollment</th>
                                <th scope="col">Status</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($courses as $course)
                                <tr>
                                    <td>{{ $course->kode_mata_kuliah }}</td>
                                    <td>{{ $course->nama_mata_kuliah }}</td>
                                    <td>{{ $course->guruDosen?->nama_lengkap }}</td>
                                    <td>{{ $course->enrollments_count }}</td>
                                    <td>
                                        @if ($course->is_archived)
                                            <span class="badge text-bg-secondary">Diarsipkan</span>
                                        @else
                                            <span class="badge text-bg-success">Aktif</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        <a href="{{ route('admin.courses.edit', $course) }}" class="btn btn-sm btn-outline-secondary">
                                            Ubah
                                        </a>

                                        @unless ($course->is_archived)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#archive-course-{{ $course->id_mata_kuliah }}">
                                                Arsipkan
                                            </button>

                                            <div class="modal fade" id="archive-course-{{ $course->id_mata_kuliah }}" tabindex="-1" aria-labelledby="archive-course-label-{{ $course->id_mata_kuliah }}" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h3 class="modal-title h6" id="archive-course-label-{{ $course->id_mata_kuliah }}">
                                                                Arsipkan {{ $course->kode_mata_kuliah }}?
                                                            </h3>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                                                        </div>
                                                        <div class="modal-body">
                                                            Mata kuliah tidak lagi menerima enrollment baru. Riwayat enrollment
                                                            dan tugas tetap tersimpan.
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <form method="POST" action="{{ route('admin.courses.archive', $course) }}">
                                                                @csrf
                                                                @method('PATCH')
                                                                <button type="submit" class="btn btn-danger">Arsipkan</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endunless
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-body-secondary">Belum ada mata kuliah.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($courses->hasPages())
                    <div class="mt-3">{{ $courses->links() }}</div>
                @endif
            </div>
        </div>
    </div>
@endsection
