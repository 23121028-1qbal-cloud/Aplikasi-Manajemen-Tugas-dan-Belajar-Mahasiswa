@extends('layouts.app')

@section('title', 'Mata Kuliah')

@section('content')
    <div class="app-card p-4">
        <h2 class="h6 mb-3">Mata Kuliah yang Diikuti</h2>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Daftar mata kuliah yang diikuti</caption>
                <thead>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Dosen</th>
                        <th scope="col">Periode</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($enrollments as $enrollment)
                        <tr>
                            <td>{{ $enrollment->mataKuliah?->kode_mata_kuliah }}</td>
                            <td>{{ $enrollment->mataKuliah?->nama_mata_kuliah }}</td>
                            <td>{{ $enrollment->mataKuliah?->guruDosen?->nama_lengkap }}</td>
                            <td>{{ $enrollment->tahun_akademik }} &middot; {{ $enrollment->semester->value }}</td>
                            <td>
                                @if ($enrollment->mataKuliah)
                                    <a href="{{ route('mahasiswa.courses.show', $enrollment->mataKuliah) }}" class="btn btn-sm btn-outline-secondary">
                                        Detail
                                    </a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-body-secondary">Belum terdaftar pada mata kuliah mana pun.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($enrollments->hasPages())
            <div class="mt-3">{{ $enrollments->links() }}</div>
        @endif
    </div>
@endsection
