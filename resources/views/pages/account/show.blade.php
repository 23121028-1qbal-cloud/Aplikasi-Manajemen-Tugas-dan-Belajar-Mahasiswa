@extends('layouts.app')

@section('title', 'Profil Akun')

@section('content')
    <div class="app-card p-4">
        <h2 class="h5 mb-3">Informasi Akun</h2>

        <dl class="row mb-0">
            <dt class="col-sm-3">Username</dt>
            <dd class="col-sm-9">{{ $user->username }}</dd>

            <dt class="col-sm-3">Role</dt>
            <dd class="col-sm-9">{{ $user->role->label() }}</dd>

            <dt class="col-sm-3">Status</dt>
            <dd class="col-sm-9">{{ $user->is_active ? 'Aktif' : 'Nonaktif' }}</dd>

            @if ($user->mahasiswa)
                <dt class="col-sm-3">NPM</dt>
                <dd class="col-sm-9">{{ $user->mahasiswa->npm }}</dd>

                <dt class="col-sm-3">Nama Lengkap</dt>
                <dd class="col-sm-9">{{ $user->mahasiswa->nama_lengkap }}</dd>
            @endif

            @if ($user->guruDosen)
                <dt class="col-sm-3">NIP</dt>
                <dd class="col-sm-9">{{ $user->guruDosen->nip_guru }}</dd>

                <dt class="col-sm-3">Nama Lengkap</dt>
                <dd class="col-sm-9">{{ $user->guruDosen->nama_lengkap }}</dd>
            @endif
        </dl>
    </div>

    <div class="mt-3">
        <a href="{{ route('account.password.edit') }}" class="btn btn-primary">Ubah Password</a>
    </div>
@endsection
