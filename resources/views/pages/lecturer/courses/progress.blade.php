@extends('layouts.app')

@section('title', 'Rekap Progres '.$course->nama_mata_kuliah)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <p class="small text-body-secondary mb-1">{{ $course->kode_mata_kuliah }}</p>
            <h2 class="h5 mb-0">Rekap Progres: {{ $course->nama_mata_kuliah }}</h2>
        </div>
        <a href="{{ route('lecturer.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    <div class="app-card p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Rekap progres tugas akademik per mata kuliah</caption>
                <thead>
                    <tr>
                        <th scope="col">Tugas</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Status</th>
                        <th scope="col">Progres</th>
                        <th scope="col">Selesai</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>{{ $task->judul_tugas }}</td>
                            <td>{{ $task->deadline?->format('d M Y H:i') }}</td>
                            <td>
                                <span class="badge {{ $task->is_closed ? 'text-bg-secondary' : 'text-bg-success' }}">
                                    {{ $task->is_closed ? 'Ditutup' : 'Terbuka' }}
                                </span>
                            </td>
                            <td>{{ $task->progres_count }} mahasiswa</td>
                            <td>{{ $task->selesai_count }} mahasiswa</td>
                            <td>
                                <a href="{{ route('lecturer.academic-tasks.progress', $task) }}" class="btn btn-sm btn-outline-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-body-secondary">Belum ada tugas akademik untuk mata kuliah ini.</td>
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
