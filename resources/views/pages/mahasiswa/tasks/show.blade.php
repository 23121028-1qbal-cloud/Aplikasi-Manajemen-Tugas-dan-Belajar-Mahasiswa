@extends('layouts.app')

@section('title', 'Detail Tugas Personal')

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">
                    Tugas Pribadi
                    @if ($task->is_archived)
                        <span class="badge text-bg-secondary ms-1">Diarsipkan</span>
                    @endif
                </p>
                <h2 class="h5 mb-0">{{ $task->judul_tugas }}</h2>
            </div>
            <a href="{{ route('mahasiswa.tasks.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">Deskripsi</dt>
            <dd class="col-sm-9">{{ $task->deskripsi ?: '—' }}</dd>

            <dt class="col-sm-3">Mata Kuliah</dt>
            <dd class="col-sm-9">{{ $task->mataKuliah?->nama_mata_kuliah ?? 'Tanpa mata kuliah' }}</dd>

            <dt class="col-sm-3">Deadline</dt>
            <dd class="col-sm-9">{{ $task->deadline?->format('d M Y H:i') }}</dd>

            <dt class="col-sm-3">Prioritas</dt>
            <dd class="col-sm-9">{{ $task->prioritas?->nama_prioritas }}</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">{{ $task->statusTugas?->nama_status }}</dd>

            <dt class="col-sm-3">Terakhir Diubah</dt>
            <dd class="col-sm-9">{{ $task->updated_at?->format('d M Y H:i') }}</dd>
        </dl>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ route('mahasiswa.tasks.personal.edit', $task) }}" class="btn btn-primary">Ubah Tugas</a>

        @unless ($task->is_archived)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#archive-task-{{ $task->id_tugas }}">
                Arsipkan
            </button>

            <x-confirm-modal
                id="archive-task-{{ $task->id_tugas }}"
                title="Arsipkan tugas ini?"
                message="Tugas dipindahkan ke riwayat dan tidak muncul pada daftar aktif. Data tidak dihapus."
                :action="route('mahasiswa.tasks.personal.archive', $task)"
                method="PATCH"
                confirm="Arsipkan"
            />
        @endunless
    </div>
@endsection
