@extends('layouts.app')

@section('title', 'Tambah Tugas Personal')

@section('content')
    <div class="app-card p-4" style="max-width: 760px;">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">Tugas Personal</p>
                <h2 class="h5 mb-0">Tambah Tugas</h2>
            </div>
            <a href="{{ route('mahasiswa.tasks.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        @include('pages.mahasiswa.tasks.form')
    </div>
@endsection
