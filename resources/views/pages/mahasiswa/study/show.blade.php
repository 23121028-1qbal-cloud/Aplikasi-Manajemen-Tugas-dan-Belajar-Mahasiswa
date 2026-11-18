@extends('layouts.app')

@section('title', 'Detail Aktivitas Belajar')

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">
                    Aktivitas Belajar
                    @if ($activity->is_archived)
                        <span class="badge text-bg-secondary ms-1">Diarsipkan</span>
                    @endif
                </p>
                <h2 class="h5 mb-0">{{ $activity->judul_aktivitas }}</h2>
            </div>
            <a href="{{ route('mahasiswa.study-activities.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">Materi</dt>
            <dd class="col-sm-9">{{ $activity->materi_belajar ?: '—' }}</dd>

            <dt class="col-sm-3">Mata Kuliah</dt>
            <dd class="col-sm-9">{{ $activity->mataKuliah?->nama_mata_kuliah ?? 'Tanpa mata kuliah' }}</dd>

            <dt class="col-sm-3">Waktu Mulai</dt>
            <dd class="col-sm-9">{{ $activity->waktu_mulai?->format('d M Y H:i') }}</dd>

            <dt class="col-sm-3">Durasi</dt>
            <dd class="col-sm-9">{{ $activity->durasi_menit }} menit</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">{{ $activity->statusBelajar?->nama_status }}</dd>

            <dt class="col-sm-3">Dibuat</dt>
            <dd class="col-sm-9">{{ $activity->tanggal_dibuat?->format('d M Y') }}</dd>
        </dl>
    </div>

    <div class="d-flex flex-wrap gap-2 mt-3">
        <a href="{{ route('mahasiswa.study-activities.edit', $activity) }}" class="btn btn-primary">Ubah Aktivitas</a>

        @unless ($activity->is_archived)
            <button type="button" class="btn btn-outline-danger" data-bs-toggle="modal" data-bs-target="#archive-activity-{{ $activity->id_aktivitas }}">
                Arsipkan
            </button>

            <x-confirm-modal
                id="archive-activity-{{ $activity->id_aktivitas }}"
                title="Arsipkan aktivitas ini?"
                message="Aktivitas dipindahkan ke riwayat dan tidak muncul pada daftar aktif. Data tidak dihapus."
                :action="route('mahasiswa.study-activities.archive', $activity)"
                method="PATCH"
                confirm="Arsipkan"
            />
        @endunless
    </div>
@endsection
