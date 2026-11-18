@extends('layouts.app')

@section('title', 'Semua Tugas')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h2 class="h5 mb-1">Tugas Personal</h2>
            <p class="small text-body-secondary mb-0">Diurutkan berdasarkan deadline terdekat.</p>
        </div>
        <a href="{{ route('mahasiswa.tasks.personal.create') }}" class="btn btn-primary">Tambah Tugas</a>
    </div>

    <div class="app-card p-4">
        <form method="GET" action="{{ route('mahasiswa.tasks.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-12 col-md-4">
                <label for="q" class="form-label">Cari Judul</label>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm">
            </div>

            <div class="col-6 col-md-3">
                <label for="id_status" class="form-label">Status</label>
                <select id="id_status" name="id_status" class="form-select form-select-sm">
                    <option value="">Semua status</option>
                    @foreach ($statuses as $status)
                        <option value="{{ $status->id_status }}" @selected(($filters['id_status'] ?? '') == $status->id_status)>
                            {{ $status->nama_status }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-3">
                <label for="id_prioritas" class="form-label">Prioritas</label>
                <select id="id_prioritas" name="id_prioritas" class="form-select form-select-sm">
                    <option value="">Semua prioritas</option>
                    @foreach ($priorities as $priority)
                        <option value="{{ $priority->id_prioritas }}" @selected(($filters['id_prioritas'] ?? '') == $priority->id_prioritas)>
                            {{ $priority->nama_prioritas }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary flex-grow-1">Terapkan</button>
                <a href="{{ route('mahasiswa.tasks.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Daftar tugas personal</caption>
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Mata Kuliah</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Prioritas</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>
                                <a href="{{ route('mahasiswa.tasks.personal.show', $task) }}">{{ $task->judul_tugas }}</a>
                                <span class="badge text-bg-light border ms-1">Tugas Pribadi</span>
                            </td>
                            <td>{{ $task->mataKuliah?->nama_mata_kuliah ?? '—' }}</td>
                            <td>{{ $task->deadline?->format('d M Y H:i') }}</td>
                            <td>
                                @php
                                    $priorityClass = match ($task->prioritas?->nama_prioritas) {
                                        'Tinggi' => 'text-bg-danger',
                                        'Sedang' => 'text-bg-info',
                                        default => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $priorityClass }}">{{ $task->prioritas?->nama_prioritas }}</span>
                            </td>
                            <td>
                                @php
                                    $statusClass = match ($task->statusTugas?->nama_status) {
                                        'Selesai' => 'text-bg-success',
                                        'Sedang Dikerjakan' => 'text-bg-warning',
                                        default => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $task->statusTugas?->nama_status }}</span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('mahasiswa.tasks.personal.edit', $task) }}" class="btn btn-sm btn-outline-secondary">
                                    Ubah
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-body-secondary">
                                Belum ada tugas personal aktif.
                                <a href="{{ route('mahasiswa.tasks.personal.create') }}">Tambah tugas pertama</a>.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tasks->hasPages())
            <div class="mt-3">{{ $tasks->links() }}</div>
        @endif

    </div>

    <div class="app-card p-4 mt-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h3 class="h6 mb-1">Tugas Akademik</h3>
                <p class="small text-body-secondary mb-0">Tugas dari dosen pada mata kuliah yang Anda ikuti.</p>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Daftar tugas akademik dan progres saya</caption>
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Mata Kuliah</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Prioritas</th>
                        <th scope="col">Progres</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($academicProgress as $row)
                        <tr>
                            <td>
                                <a href="{{ route('mahasiswa.academic-tasks.show', $row->tugas) }}">{{ $row->tugas?->judul_tugas }}</a>
                                @if ($row->tugas?->is_closed)
                                    <span class="badge text-bg-secondary ms-1">Ditutup</span>
                                @endif
                            </td>
                            <td>{{ $row->tugas?->mataKuliah?->nama_mata_kuliah ?? '—' }}</td>
                            <td>{{ $row->tugas?->deadline?->format('d M Y H:i') }}</td>
                            <td>{{ $row->tugas?->prioritas?->nama_prioritas }}</td>
                            <td>
                                @php
                                    $academicStatusClass = match ($row->statusTugas?->nama_status) {
                                        'Selesai' => 'text-bg-success',
                                        'Sedang Dikerjakan' => 'text-bg-warning',
                                        default => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $academicStatusClass }}">{{ $row->statusTugas?->nama_status }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-body-secondary">Belum ada tugas akademik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($academicProgress->hasPages())
            <div class="mt-3">{{ $academicProgress->links() }}</div>
        @endif
    </div>
@endsection
