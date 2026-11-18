@extends('layouts.app')

@section('title', $task->judul_tugas)

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
        <div>
            <p class="small text-body-secondary mb-1">Monitoring Read-only</p>
            <h2 class="h5 mb-0">{{ $task->judul_tugas }}</h2>
        </div>
        <a href="{{ route('admin.academic-monitoring.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
    </div>

    <div class="app-card p-4">
        <dl class="row mb-0">
            <dt class="col-sm-3">Mata Kuliah</dt>
            <dd class="col-sm-9">{{ $task->mataKuliah?->kode_mata_kuliah }} — {{ $task->mataKuliah?->nama_mata_kuliah }}</dd>

            <dt class="col-sm-3">Dosen</dt>
            <dd class="col-sm-9">{{ $task->mataKuliah?->guruDosen?->nama_lengkap }}</dd>

            <dt class="col-sm-3">Deskripsi</dt>
            <dd class="col-sm-9">{{ $task->deskripsi ?: '—' }}</dd>

            <dt class="col-sm-3">Deadline</dt>
            <dd class="col-sm-9">{{ $task->deadline?->format('d M Y H:i') }}</dd>

            <dt class="col-sm-3">Prioritas</dt>
            <dd class="col-sm-9">{{ $task->prioritas?->nama_prioritas }}</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">{{ $task->is_closed ? 'Ditutup' : 'Terbuka' }}</dd>
        </dl>
    </div>

    <div class="app-card p-4 mt-3">
        <h3 class="h6">Progres Mahasiswa</h3>
        <p class="small text-body-secondary">Admin tidak dapat mengubah progres dari halaman ini.</p>

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
                    </tr>
                </thead>
                <tbody>
                    @forelse ($progress as $row)
                        <tr>
                            <td>{{ $row->mahasiswa?->nama_lengkap }}</td>
                            <td>{{ $row->mahasiswa?->npm }}</td>
                            <td>{{ $row->statusTugas?->nama_status }}</td>
                            <td>{{ $row->diubahOleh?->username ?? '—' }}</td>
                            <td>{{ $row->tanggal_ubah?->format('d M Y H:i') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-body-secondary">Belum ada progres mahasiswa.</td>
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
