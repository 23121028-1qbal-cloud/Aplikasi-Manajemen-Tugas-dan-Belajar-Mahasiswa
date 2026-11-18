@extends('layouts.app')

@section('title', $course->nama_mata_kuliah)

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-start gap-2 mb-3">
            <div>
                <p class="small text-body-secondary mb-1">{{ $course->kode_mata_kuliah }}</p>
                <h2 class="h5 mb-0">{{ $course->nama_mata_kuliah }}</h2>
            </div>
            <a href="{{ route('mahasiswa.courses.index') }}" class="btn btn-sm btn-outline-secondary">Kembali</a>
        </div>

        <dl class="row mb-0">
            <dt class="col-sm-3">Dosen Pengampu</dt>
            <dd class="col-sm-9">{{ $course->guruDosen?->nama_lengkap }}</dd>
        </dl>
    </div>

    <div class="app-card p-4 mt-3">
        <h3 class="h6">Tugas Akademik</h3>
        <p class="small text-body-secondary">Progres Anda pada tugas akademik mata kuliah ini.</p>

        @if ($progress->isEmpty())
            <p class="small text-body-secondary mb-0">Belum ada tugas akademik untuk mata kuliah ini.</p>
        @else
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <caption class="visually-hidden">Tugas akademik dan progres saya</caption>
                    <thead>
                        <tr>
                            <th scope="col">Judul</th>
                            <th scope="col">Deadline</th>
                            <th scope="col">Prioritas</th>
                            <th scope="col">Progres</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($progress as $row)
                            <tr>
                                <td>
                                    <a href="{{ route('mahasiswa.academic-tasks.show', $row->tugas) }}">{{ $row->tugas?->judul_tugas }}</a>
                                    @if ($row->tugas?->is_closed)
                                        <span class="badge text-bg-secondary ms-1">Ditutup</span>
                                    @endif
                                </td>
                                <td>{{ $row->tugas?->deadline?->format('d M Y H:i') }}</td>
                                <td>{{ $row->tugas?->prioritas?->nama_prioritas }}</td>
                                <td>{{ $row->statusTugas?->nama_status }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
