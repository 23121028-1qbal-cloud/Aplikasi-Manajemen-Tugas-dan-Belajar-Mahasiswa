@extends('layouts.app')

@section('title', 'Ubah Mata Kuliah')

@section('content')
    <div class="app-card p-4" style="max-width: 640px;">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">Mata Kuliah</p>
                <h2 class="h5 mb-0">{{ $course->kode_mata_kuliah }}</h2>
            </div>
            <a href="{{ route('admin.courses.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        @if ($course->is_archived)
            <div class="alert alert-secondary" role="status">
                Mata kuliah ini sudah diarsipkan pada {{ $course->tanggal_arsip?->format('d M Y') }}.
            </div>
        @endif

        <form method="POST" action="{{ route('admin.courses.update', $course) }}">
            @csrf
            @method('PUT')
            <input type="hidden" name="row_version" value="{{ old('row_version', $course->row_version) }}">

            @if ($errors->has('row_version'))
                <div class="alert alert-warning" role="alert">
                    {{ $errors->first('row_version') }}
                    <a href="{{ route('admin.courses.edit', $course) }}" class="alert-link">Muat versi terbaru</a>.
                </div>
            @endif

            <div class="mb-3">
                <label for="kode_mata_kuliah" class="form-label">Kode Mata Kuliah</label>
                <input type="text"
                       id="kode_mata_kuliah"
                       name="kode_mata_kuliah"
                       value="{{ old('kode_mata_kuliah', $course->kode_mata_kuliah) }}"
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
                       value="{{ old('nama_mata_kuliah', $course->nama_mata_kuliah) }}"
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
                    @foreach ($lecturers as $lecturer)
                        <option value="{{ $lecturer->id_guru_dosen }}" @selected(old('guru_dosen_id', $course->guru_dosen_id) == $lecturer->id_guru_dosen)>
                            {{ $lecturer->nama_lengkap }} ({{ $lecturer->nip_guru }})
                        </option>
                    @endforeach
                </select>
                @error('guru_dosen_id')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
        </form>
    </div>
@endsection
