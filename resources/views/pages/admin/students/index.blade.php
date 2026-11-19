@extends('layouts.app')

@section('title', 'Akun Mahasiswa')

@section('content')
    <div class="app-card p-4">
        <div class="d-flex flex-wrap align-items-end justify-content-between gap-2 mb-3">
            <h2 class="h6 mb-0">Daftar Akun Mahasiswa</h2>

            <form method="GET" action="{{ route('admin.students.index') }}" class="d-flex gap-2" role="search">
                <label for="q" class="visually-hidden">Cari mahasiswa</label>
                <input type="search"
                       id="q"
                       name="q"
                       value="{{ $search }}"
                       class="form-control form-control-sm"
                       placeholder="Nama, NPM, atau username">
                <button type="submit" class="btn btn-sm btn-outline-secondary">Cari</button>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <caption class="visually-hidden">Daftar akun mahasiswa</caption>
                <thead>
                    <tr>
                        <th scope="col">Nama</th>
                        <th scope="col">NPM</th>
                        <th scope="col">Username</th>
                        <th scope="col">Status</th>
                        <th scope="col">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($students as $student)
                        <tr>
                            <td>{{ $student->nama_lengkap }}</td>
                            <td>{{ $student->npm }}</td>
                            <td>{{ $student->user?->username }}</td>
                            <td>
                                @if ($student->is_archived)
                                    <span class="badge text-bg-dark">Diarsipkan</span>
                                @elseif ($student->user?->is_active)
                                    <span class="badge text-bg-success">Aktif</span>
                                @else
                                    <span class="badge text-bg-secondary">Nonaktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('admin.students.show', $student) }}" class="btn btn-sm btn-outline-secondary">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-body-secondary">Tidak ada akun mahasiswa yang cocok.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($students->hasPages())
            <div class="mt-3">{{ $students->links() }}</div>
        @endif
    </div>
@endsection
