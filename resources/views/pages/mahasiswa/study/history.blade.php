@extends('layouts.app')

@section('title', 'Riwayat Belajar')

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div>
                <h2 class="h5 mb-1">Riwayat Belajar</h2>
                <p class="small text-body-secondary mb-0">Aktivitas selesai dan diarsipkan tetap tersimpan di sini.</p>
            </div>
            <a href="{{ route('mahasiswa.study-activities.index') }}" class="btn btn-sm btn-outline-secondary">Daftar Aktif</a>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Riwayat aktivitas belajar</caption>
                <thead>
                    <tr>
                        <th scope="col">Judul</th>
                        <th scope="col">Mata Kuliah</th>
                        <th scope="col">Waktu Mulai</th>
                        <th scope="col">Durasi</th>
                        <th scope="col">Status</th>
                        <th scope="col">Arsip</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $activity)
                        <tr>
                            <td>{{ $activity->judul_aktivitas }}</td>
                            <td>{{ $activity->mataKuliah?->nama_mata_kuliah ?? '—' }}</td>
                            <td>{{ $activity->waktu_mulai?->format('d M Y H:i') }}</td>
                            <td>{{ $activity->durasi_menit }} menit</td>
                            <td>{{ $activity->statusBelajar?->nama_status }}</td>
                            <td>
                                @if ($activity->is_archived)
                                    {{ $activity->tanggal_arsip?->format('d M Y') ?? 'Ya' }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-body-secondary">Belum ada aktivitas pada riwayat.</td>
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
