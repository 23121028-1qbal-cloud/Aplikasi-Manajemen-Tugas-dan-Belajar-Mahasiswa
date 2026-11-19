@extends('layouts.app')

@section('title', $course->nama_mata_kuliah)

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">{{ $course->kode_mata_kuliah }}</p>
                <h2 class="h5 mb-0">{{ $course->nama_mata_kuliah }}</h2>
            </div>
            <a href="{{ route('lecturer.courses.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">Dosen Pengampu</dt>
            <dd class="col-sm-9">{{ $course->guruDosen?->nama_lengkap }}</dd>

            <dt class="col-sm-3">Mahasiswa Terdaftar</dt>
            <dd class="col-sm-9">{{ $course->enrollments_count }}</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">
                @if ($course->is_archived)
                    <span class="badge text-bg-secondary">Diarsipkan</span>
                @else
                    <span class="badge text-bg-success">Aktif</span>
                @endif
            </dd>
        </dl>
    </div>

    <div class="app-card p-4 mt-3">
        <h3 class="h6">Ubah Nama Mata Kuliah</h3>
        <p class="small text-body-secondary">
            Kode mata kuliah dan dosen pengampu hanya dapat diubah oleh Admin.
        </p>

        <form method="POST" action="{{ route('lecturer.courses.update', $course) }}" style="max-width: 520px;">
            @csrf
            @method('PUT')
            <input type="hidden" name="row_version" value="{{ old('row_version', $course->row_version) }}">

            @if ($errors->has('row_version'))
                <div class="alert alert-warning" role="alert">
                    {{ $errors->first('row_version') }}
                    <a href="{{ route('lecturer.courses.show', $course) }}" class="alert-link">Muat versi terbaru</a>.
                </div>
            @endif

            <div class="mb-3">
                <label for="nama_mata_kuliah" class="form-label">Nama Mata Kuliah</label>
                <input type="text"
                       id="nama_mata_kuliah"
                       name="nama_mata_kuliah"
                       value="{{ old('nama_mata_kuliah', $course->nama_mata_kuliah) }}"
                       class="form-control @error('nama_mata_kuliah') is-invalid @enderror"
                       maxlength="100"
                       required>
                @error('nama_mata_kuliah')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-sm btn-primary">Simpan Nama</button>
        </form>
    </div>

    <div class="app-card p-4 mt-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <h3 class="h6 mb-0">Tugas Akademik</h3>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('lecturer.courses.progress', $course) }}" class="btn btn-sm btn-outline-secondary">Rekap Progres</a>
                <a href="{{ route('lecturer.academic-tasks.create') }}" class="btn btn-sm btn-primary">Buat Tugas</a>
            </div>
        </div>

        @if ($tasks->isEmpty())
            <p class="small text-body-secondary mb-0">Belum ada tugas akademik untuk mata kuliah ini.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <caption class="visually-hidden">Tugas akademik mata kuliah</caption>
                    <thead>
                        <tr>
                            <th scope="col">Judul</th>
                            <th scope="col">Deadline</th>
                            <th scope="col">Progres</th>
                            <th scope="col">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($tasks as $task)
                            <tr>
                                <td><a href="{{ route('lecturer.academic-tasks.show', $task) }}">{{ $task->judul_tugas }}</a></td>
                                <td>{{ $task->deadline?->format('d M Y H:i') }}</td>
                                <td>{{ $task->progres_count }} mahasiswa</td>
                                <td>{{ $task->is_closed ? 'Ditutup' : 'Terbuka' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
