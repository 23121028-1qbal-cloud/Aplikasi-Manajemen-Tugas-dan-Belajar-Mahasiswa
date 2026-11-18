@extends('layouts.app')

@section('title', 'Progres '.$task->judul_tugas)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <p class="small text-body-secondary mb-1">{{ $task->mataKuliah?->kode_mata_kuliah }} — {{ $task->mataKuliah?->nama_mata_kuliah }}</p>
            <h2 class="h5 mb-0">Progres: {{ $task->judul_tugas }}</h2>
        </div>
        <a href="{{ route('lecturer.academic-tasks.show', $task) }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    @if ($errors->has('row_version'))
        <div class="alert alert-warning" role="alert">
            {{ $errors->first('row_version') }}
            <a href="{{ route('lecturer.academic-tasks.progress', $task) }}" class="alert-link">Muat versi terbaru</a>.
        </div>
    @endif

    <div class="app-card p-4">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Progres mahasiswa untuk tugas akademik</caption>
                <thead>
                    <tr>
                        <th scope="col">Mahasiswa</th>
                        <th scope="col">NPM</th>
                        <th scope="col">Status</th>
                        <th scope="col">Diubah Oleh</th>
                        <th scope="col">Tanggal Ubah</th>
                        <th scope="col">Koreksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($progress as $row)
                        <tr>
                            <td>{{ $row->mahasiswa?->nama_lengkap }}</td>
                            <td>{{ $row->mahasiswa?->npm }}</td>
                            <td>
                                @php
                                    $statusClass = match ($row->statusTugas?->nama_status) {
                                        'Selesai' => 'text-bg-success',
                                        'Sedang Dikerjakan' => 'text-bg-warning',
                                        default => 'text-bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $statusClass }}">{{ $row->statusTugas?->nama_status }}</span>
                            </td>
                            <td>{{ $row->diubahOleh?->username ?? '—' }}</td>
                            <td>{{ $row->tanggal_ubah?->format('d M Y H:i') }}</td>
                            <td>
                                <form method="POST"
                                      action="{{ route('lecturer.progress.update', $row) }}"
                                      class="d-flex flex-wrap align-items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="row_version" value="{{ $row->row_version }}">
                                    <label class="visually-hidden" for="status-{{ $row->id_progress }}">Status {{ $row->mahasiswa?->nama_lengkap }}</label>
                                    <select id="status-{{ $row->id_progress }}" name="id_status" class="form-select form-select-sm" style="max-width: 190px;">
                                        @foreach ($statuses as $status)
                                            <option value="{{ $status->id_status }}" @selected($row->id_status === $status->id_status)>
                                                {{ $status->nama_status }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">Simpan</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-body-secondary">Belum ada progres mahasiswa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($progress->hasPages())
            <div class="mt-3">{{ $progress->links() }}</div>
        @endif
    </div>
@endsection
