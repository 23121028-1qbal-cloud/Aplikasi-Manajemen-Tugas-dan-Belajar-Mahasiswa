@extends('layouts.app')

@section('title', 'Mata Kuliah Saya')

@section('content')
    <div class="app-card p-4">
        <h2 class="h6 mb-3">Mata Kuliah yang Diampu</h2>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Daftar mata kuliah yang diampu</caption>
                <thead>
                    <tr>
                        <th scope="col">Kode</th>
                        <th scope="col">Nama</th>
                        <th scope="col">Mahasiswa Terdaftar</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($courses as $course)
                        <tr>
                            <td>{{ $course->kode_mata_kuliah }}</td>
                            <td>{{ $course->nama_mata_kuliah }}</td>
                            <td>{{ $course->enrollments_count }}</td>
                            <td>
                                @if ($course->is_archived)
                                    <span class="badge text-bg-secondary">Diarsipkan</span>
                                @else
                                    <span class="badge text-bg-success">Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('lecturer.courses.show', $course) }}" class="btn btn-sm btn-outline-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-body-secondary">Belum ada mata kuliah yang ditugaskan.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($courses->hasPages())
            <div class="mt-3">{{ $courses->links() }}</div>
        @endif
    </div>
@endsection
