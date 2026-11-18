@extends('layouts.app')

@section('title', 'Buat Tugas Akademik')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Buat Tugas Akademik</h2>
            <p class="small text-body-secondary mb-0">
                Progres awal <strong>Belum Dikerjakan</strong> dibuat untuk setiap mahasiswa dengan enrollment Terdaftar.
            </p>
        </div>
        <a href="{{ route('lecturer.academic-tasks.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    <div class="app-card p-4">
        @if ($courses->isEmpty())
            <p class="text-body-secondary mb-0">Belum ada mata kuliah aktif yang diampu.</p>
        @else
            @include('pages.lecturer.academic-tasks._form')
        @endif
    </div>
@endsection
