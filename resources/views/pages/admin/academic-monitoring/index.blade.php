@extends('layouts.app')

@section('title', 'Monitoring Akademik')

@section('content')
    <div class="mb-3">
        <h2 class="h5 mb-1">Monitoring Akademik</h2>
        <p class="small text-body-secondary mb-0">
            Hanya definisi tugas akademik dan progres. Tugas personal dan aktivitas belajar tidak ditampilkan.
        </p>
    </div>

    <div class="app-card p-4">
        <form method="GET" action="{{ route('admin.academic-monitoring.index') }}" class="row g-2 align-items-end mb-3">
            <div class="col-12 col-md-5">
                <label for="q" class="form-label">Cari Judul</label>
                <input type="search" id="q" name="q" value="{{ $filters['q'] ?? '' }}" class="form-control form-control-sm">
            </div>

            <div class="col-12 col-md-5">
                <label for="id_mata_kuliah" class="form-label">Mata Kuliah</label>
                <select id="id_mata_kuliah" name="id_mata_kuliah" class="form-select form-select-sm">
                    <option value="">Semua mata kuliah</option>
                    @foreach ($courses as $course)
                        <option value="{{ $course->id_mata_kuliah }}" @selected(($filters['id_mata_kuliah'] ?? '') == $course->id_mata_kuliah)>
                            {{ $course->kode_mata_kuliah }} — {{ $course->nama_mata_kuliah }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-outline-secondary flex-grow-1">Terapkan</button>
                <a href="{{ route('admin.academic-monitoring.index') }}" class="btn btn-sm btn-outline-secondary">Reset</a>
            </div>
        </form>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Daftar tugas akademik untuk monitoring</caption>
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Mata Kuliah</th>
                        <th scope="col">Dosen</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Progres</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td><a href="{{ route('admin.academic-monitoring.tasks.show', $task) }}">{{ $task->judul_tugas }}</a></td>
                            <td>{{ $task->mataKuliah?->kode_mata_kuliah }} — {{ $task->mataKuliah?->nama_mata_kuliah }}</td>
                            <td>{{ $task->mataKuliah?->guruDosen?->nama_lengkap }}</td>
                            <td>{{ $task->deadline?->format('d M Y H:i') }}</td>
                            <td>{{ $task->progres_count }} mahasiswa, {{ $task->selesai_count }} selesai</td>
                            <td>
                                @if ($task->is_closed)
                                    <span class="badge text-bg-secondary">Ditutup</span>
                                @else
                                    <span class="badge text-bg-success">Terbuka</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-body-secondary">Belum ada tugas akademik.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($tasks->hasPages())
            <div class="mt-3">{{ $tasks->links() }}</div>
        @endif
    </div>
@endsection
