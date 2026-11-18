@extends('layouts.app')

@section('title', 'Riwayat Tugas')

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">Riwayat Tugas</h2>
                <p class="small text-body-secondary mb-0">Tugas selesai dan diarsipkan tetap tersimpan di sini.</p>
            </div>
            <a href="{{ route('mahasiswa.tasks.index') }}" class="btn btn-sm btn-outline-secondary">Daftar Aktif</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Riwayat tugas personal</caption>
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Mata Kuliah</th>
                        <th scope="col">Deadline</th>
                        <th scope="col">Status</th>
                        <th scope="col">Arsip</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($tasks as $task)
                        <tr>
                            <td>{{ $task->judul_tugas }}</td>
                            <td>{{ $task->mataKuliah?->nama_mata_kuliah ?? '—' }}</td>
                            <td>{{ $task->deadline?->format('d M Y H:i') }}</td>
                            <td>{{ $task->statusTugas?->nama_status }}</td>
                            <td>
                                @if ($task->is_archived)
                                    {{ $task->tanggal_arsip?->format('d M Y') ?? 'Ya' }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-body-secondary">Belum ada tugas pada riwayat.</td>
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
