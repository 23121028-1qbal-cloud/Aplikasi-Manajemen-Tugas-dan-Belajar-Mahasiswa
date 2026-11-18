@extends('layouts.app')

@section('title', 'Enrollment')

@section('content')
    <div class="row g-3">
        <div class="col-12 col-xl-4">
            <div class="app-card p-4">
                <h2 class="h6 mb-3">Tambah Enrollment</h2>

                @if ($students->isEmpty() || $courses->isEmpty())
                    <p class="small text-body-secondary mb-0">
                        Enrollment memerlukan minimal satu akun mahasiswa dan satu mata kuliah aktif.
                    </p>
                @else
                    <form method="POST" action="{{ route('admin.enrollments.store') }}">
                        @csrf

                        <div class="mb-3">
                            <label for="id_mahasiswa" class="form-label">Mahasiswa</label>
                            <select id="id_mahasiswa"
                                    name="id_mahasiswa"
                                    class="form-select @error('id_mahasiswa') is-invalid @enderror"
                                    required>
                                <option value="">Pilih mahasiswa</option>
                                @foreach ($students as $student)
                                    <option value="{{ $student->id_mahasiswa }}" @selected(old('id_mahasiswa') == $student->id_mahasiswa)>
                                        {{ $student->nama_lengkap }} ({{ $student->npm }})
                                    </option>
                                @endforeach
                            </select>
                            @error('id_mahasiswa')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="id_mata_kuliah" class="form-label">Mata Kuliah</label>
                            <select id="id_mata_kuliah"
                                    name="id_mata_kuliah"
                                    class="form-select @error('id_mata_kuliah') is-invalid @enderror"
                                    required>
                                <option value="">Pilih mata kuliah</option>
                                @foreach ($courses as $course)
                                    <option value="{{ $course->id_mata_kuliah }}" @selected(old('id_mata_kuliah') == $course->id_mata_kuliah)>
                                        {{ $course->kode_mata_kuliah }} — {{ $course->nama_mata_kuliah }}
                                    </option>
                                @endforeach
                            </select>
                            @error('id_mata_kuliah')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="tahun_akademik" class="form-label">Tahun Akademik</label>
                            <input type="text"
                                   id="tahun_akademik"
                                   name="tahun_akademik"
                                   value="{{ old('tahun_akademik') }}"
                                   class="form-control @error('tahun_akademik') is-invalid @enderror"
                                   placeholder="2026/2027"
                                   required>
                            @error('tahun_akademik')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <label for="semester" class="form-label">Semester</label>
                            <select id="semester"
                                    name="semester"
                                    class="form-select @error('semester') is-invalid @enderror"
                                    required>
                                <option value="">Pilih semester</option>
                                @foreach ($semesters as $semester)
                                    <option value="{{ $semester->value }}" @selected(old('semester') === $semester->value)>
                                        {{ $semester->value }}
                                    </option>
                                @endforeach
                            </select>
                            @error('semester')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary">Simpan Enrollment</button>
                    </form>
                @endif
            </div>
        </div>

        <div class="col-12 col-xl-8">
            <div class="app-card p-4">
                <h2 class="h6 mb-3">Daftar Enrollment</h2>

                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <caption class="visually-hidden">Daftar enrollment mahasiswa</caption>
                        <thead>
                            <tr>
                                <th scope="col">Mahasiswa</th>
                                <th scope="col">Mata Kuliah</th>
                                <th scope="col">Periode</th>
                                <th scope="col">Status</th>
                                <th scope="col">Terdaftar</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($enrollments as $enrollment)
                                <tr>
                                    <td>
                                        {{ $enrollment->mahasiswa?->nama_lengkap }}
                                        <span class="d-block small text-body-secondary">{{ $enrollment->mahasiswa?->npm }}</span>
                                    </td>
                                    <td>
                                        {{ $enrollment->mataKuliah?->nama_mata_kuliah }}
                                        <span class="d-block small text-body-secondary">{{ $enrollment->mataKuliah?->kode_mata_kuliah }}</span>
                                    </td>
                                    <td>{{ $enrollment->tahun_akademik }} &middot; {{ $enrollment->semester->value }}</td>
                                    <td>
                                        @php
                                            $statusClass = match ($enrollment->status->value) {
                                                'Terdaftar' => 'text-bg-success',
                                                'Ditolak' => 'text-bg-danger',
                                                'Selesai' => 'text-bg-info',
                                                default => 'text-bg-secondary',
                                            };
                                        @endphp
                                        <span class="badge {{ $statusClass }}">{{ $enrollment->status->value }}</span>
                                    </td>
                                    <td>{{ $enrollment->tanggal_daftar?->format('d M Y') }}</td>
                                    <td class="text-nowrap">
                                        @if ($enrollment->status === \App\Enums\EnrollmentStatus::Terdaftar)
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-danger"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#enrollment-cancel-{{ $enrollment->id_mahasiswa_mata_kuliah }}">
                                                Batalkan
                                            </button>
                                            <button type="button"
                                                    class="btn btn-sm btn-outline-secondary"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#enrollment-complete-{{ $enrollment->id_mahasiswa_mata_kuliah }}">
                                                Selesaikan
                                            </button>
                                        @else
                                            <span class="small text-body-secondary">Aktifkan lewat form periode yang sama.</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-body-secondary">Belum ada enrollment.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if ($enrollments->hasPages())
                    <div class="mt-3">{{ $enrollments->links() }}</div>
                @endif

                @foreach ($enrollments as $enrollment)
                    @if ($enrollment->status === \App\Enums\EnrollmentStatus::Terdaftar)
                        <x-confirm-modal
                            id="enrollment-cancel-{{ $enrollment->id_mahasiswa_mata_kuliah }}"
                            title="Batalkan enrollment?"
                            message="Mahasiswa tidak lagi tercatat aktif pada mata kuliah ini. Riwayat progres akademik tetap tersimpan."
                            :action="route('admin.enrollments.update', $enrollment)"
                            method="PUT"
                            confirm="Batalkan Enrollment"
                        >
                            <input type="hidden" name="status" value="Dibatalkan">
                        </x-confirm-modal>

                        <x-confirm-modal
                            id="enrollment-complete-{{ $enrollment->id_mahasiswa_mata_kuliah }}"
                            title="Selesaikan enrollment?"
                            message="Status periode ini menjadi Selesai. Riwayat enrollment dan progres tetap tersimpan."
                            :action="route('admin.enrollments.update', $enrollment)"
                            method="PUT"
                            confirm="Selesaikan Enrollment"
                            variant="primary"
                        >
                            <input type="hidden" name="status" value="Selesai">
                        </x-confirm-modal>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
@endsection
