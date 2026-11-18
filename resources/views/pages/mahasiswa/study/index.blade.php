@extends('layouts.app')

@section('title', 'Aktivitas Belajar')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Aktivitas Belajar</h2>
            <p class="small text-body-secondary mb-0">Diurutkan berdasarkan waktu mulai terdekat.</p>
        </div>
        <a href="{{ route('mahasiswa.study-activities.create') }}" class="btn btn-primary">Tambah Aktivitas</a>
    </div>

    <div class="app-card p-4">
        <form method="GET" action="{{ route('mahasiswa.study-activities.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-12 col-md-5">
                <label for="q" class="form-label">Cari Judul</label>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm">
            </div>

            <div class="col-8 col-md-4">
                <label for="id_status_belajar" class="form-label">Status</label>
                <select id="id_status_belajar" name="id_status_belajar" class="form-select form-select-sm">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id_status_belajar }}" @selected(($filters['id_status_belajar'] ?? '') == $status->id_status_belajar)>
                            {{ $status->nama_status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-4 col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary flex-grow-1">Terapkan</button>
                <a href="{{ route('mahasiswa.study-activities.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Daftar aktivitas belajar</caption>
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Mata Kuliah</th>
                        <th scope="col">Waktu Mulai</th>
                        <th scope="col">Durasi</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>
                                <a href="{{ route('mahasiswa.study-activities.show', $activity) }}">{{ $activity->judul_aktivitas }}</a>
                            </td>
                            <td>{{ $activity->mataKuliah?->nama_mata_kuliah ?? '—' }}</td>
                            <td>{{ $activity->waktu_mulai?->format('d M Y H:i') }}</td>
                            <td>{{ $activity->durasi_menit }} menit</td>
                            <td>
                                @php
                                    $statusClass = match ($activity->statusBelajar?->nama_status) {
                                        'Selesai' => 'text-bg-success',
                                        'Sedang Berlangsung' => 'text-bg-warning',
                                        default => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $activity->statusBelajar?->nama_status }}</span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('mahasiswa.study-activities.edit', $activity) }}" class="btn btn-sm btn-outline-secondary">
                                    Ubah
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-body-secondary">
                                Belum ada aktivitas belajar aktif.
                                <a href="{{ route('mahasiswa.study-activities.create') }}">Tambah aktivitas pertama</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($activities->hasPages())
            <div class="mt-3">{{ $activities->links() }}</div>
        @endif
    </div>
@endsection
