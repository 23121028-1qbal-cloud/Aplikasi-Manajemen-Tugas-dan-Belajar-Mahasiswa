@extends('layouts.app')

@section('title', 'Ubah Tugas Akademik')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Ubah Tugas Akademik</h2>
            <p class="small text-body-secondary mb-0">Perubahan definisi tidak mengubah progres yang sudah ada.</p>
        </div>
        <a href="{{ route('lecturer.academic-tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    <div class="app-card p-4">
        @include('pages.lecturer.academic-tasks._form')
    </div>
@endsection
