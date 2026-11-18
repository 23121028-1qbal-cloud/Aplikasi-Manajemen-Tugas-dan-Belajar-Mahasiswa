@extends('layouts.app')

@section('title', 'Tambah Aktivitas Belajar')

@section('content')
    <div class="app-card p-4" style="max-width: 760px;">
        <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">Aktivitas Belajar</p>
                <h2 class="h5 mb-0">Tambah Aktivitas</h2>
            </div>
            <a href="{{ route('mahasiswa.study-activities.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        @include('pages.mahasiswa.study.form')
    </div>
@endsection
