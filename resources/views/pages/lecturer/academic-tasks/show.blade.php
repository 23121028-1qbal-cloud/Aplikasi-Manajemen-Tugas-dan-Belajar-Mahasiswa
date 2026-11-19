@extends('layouts.app')

@section('title', $task->judul_tugas)

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">
                    {{ $task->mataKuliah?->kode_mata_kuliah }} — {{ $task->mataKuliah?->nama_mata_kuliah }}
                    @if ($task->is_closed)
                        <span class="badge text-bg-secondary ms-1">Ditutup</span>
                    @else
                        <span class="badge text-bg-success ms-1">Terbuka</span>
                    @endif
                </p>
                <h2 class="h5 mb-0">{{ $task->judul_tugas }}</h2>
            </div>
            <a href="{{ route('lecturer.academic-tasks.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">Deskripsi</dt>
            <dd class="col-sm-9">{{ $task->deskripsi ?: '—' }}</dd>

            <dt class="col-sm-3">Deadline</dt>
            <dd class="col-sm-9">{{ $task->deadline?->format('d M Y H:i') }}</dd>

            <dt class="col-sm-3">Prioritas</dt>
            <dd class="col-sm-9">{{ $task->prioritas?->nama_prioritas }}</dd>

            <dt class="col-sm-3">Progres</dt>
            <dd class="col-sm-9">
                {{ $task->progres_count }} mahasiswa, {{ $task->selesai_count }} selesai.
                <a href="{{ route('lecturer.academic-tasks.progress', $task) }}" class="ms-1">Lihat detail progres</a>.
            </dd>

            <dt class="col-sm-3">Terakhir Diubah</dt>
            <dd class="col-sm-9">{{ $task->updated_at?->format('d M Y H:i') }}</dd>
        </dl>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ route('lecturer.academic-tasks.edit', $task) }}" class="btn btn-primary">Ubah Definisi</a>
        <a href="{{ route('lecturer.academic-tasks.progress', $task) }}" class="btn btn-outline-secondary">Monitoring Progres</a>

        @unless ($task->is_closed)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#close-academic-{{ $task->id_tugas }}">
                Tutup Tugas
            </button>

            <x-confirm-modal
                id="close-academic-{{ $task->id_tugas }}"
                title="Tutup tugas akademik ini?"
                message="Setelah ditutup, mahasiswa tidak dapat mengubah progres dan mahasiswa yang mendaftar kemudian tidak menerima progres baru. Dosen masih dapat mengoreksi progres; riwayat tetap tersimpan."
                :action="route('lecturer.academic-tasks.close', $task)"
                method="PATCH"
                confirm="Tutup Tugas"
                variant="danger"
            />
        @endunless
    </div>
@endsection
