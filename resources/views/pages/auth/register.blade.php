@extends('layouts.guest')

@section('title', 'Registrasi Mahasiswa')

@section('content')
    <div class="app-guest-card mx-auto">
        <div class="app-card p-4 p-md-5">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-person-plus fs-3 text-primary" aria-hidden="true"></i>
                <div>
                    <h1 class="h5 mb-0">Registrasi Mahasiswa</h1>
                    <p class="small text-body-secondary mb-0">Akun ini selalu berrole Mahasiswa.</p>
                </div>
            </div>

            <form method="POST" action="{{ route('register.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="nama_lengkap" class="form-label">Nama Lengkap</label>
                    <input type="text"
                           id="nama_lengkap"
                           name="nama_lengkap"
                           value="{{ old('nama_lengkap') }}"
                           class="form-control @error('nama_lengkap') is-invalid @enderror"
                           autocomplete="name"
                           required>
                    @error('nama_lengkap')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="npm" class="form-label">NPM</label>
                    <input type="text"
                           id="npm"
                           name="npm"
                           value="{{ old('npm') }}"
                           class="form-control @error('npm') is-invalid @enderror"
                           inputmode="numeric"
                           required>
                    @error('npm')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           value="{{ old('username') }}"
                           class="form-control @error('username') is-invalid @enderror"
                           autocomplete="username"
                           required>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           autocomplete="new-password"
                           minlength="8"
                           required>
                    <div class="form-text">Minimal 8 karakter.</div>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password_confirmation" class="form-label">Ulangi Password</label>
                    <input type="password"
                           id="password_confirmation"
                           name="password_confirmation"
                           class="form-control"
                           autocomplete="new-password"
                           minlength="8"
                           required>
                </div>

                <button type="submit" class="btn btn-primary w-100">Daftar</button>
            </form>

            <p class="small text-body-secondary mt-4 mb-0">
                Sudah memiliki akun? <a href="{{ route('login') }}">Masuk</a>
            </p>
        </div>
    </div>
@endsection
