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
        <h3 class="h6">Tugas Akademik</h3>
        <p class="small text-body-secondary mb-0">
            Daftar dan pembuatan tugas akademik tersedia pada Phase 6.
        </p>
    </div>
@endsection
