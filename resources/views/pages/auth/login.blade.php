@extends('layouts.guest')

@section('title', 'Masuk')

@section('content')
    <div class="app-guest-card mx-auto">
        <div class="app-card p-4 p-md-5">
            <div class="d-flex align-items-center gap-2 mb-4">
                <i class="bi bi-journal-check fs-3 text-primary" aria-hidden="true"></i>
                <div>
                    <h1 class="h5 mb-0">{{ config('app.name') }}</h1>
                    <p class="small text-body-secondary mb-0">Masuk untuk melanjutkan</p>
                </div>
            </div>

            <form method="POST" action="{{ route('login.store') }}">
                @csrf

                <div class="mb-3">
                    <label for="username" class="form-label">Username</label>
                    <input type="text"
                           id="username"
                           name="username"
                           value="{{ old('username') }}"
                           class="form-control @error('username') is-invalid @enderror"
                           autocomplete="username"
                           required
                           autofocus>
                    @error('username')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label for="password" class="form-label">Password</label>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-control @error('password') is-invalid @enderror"
                           autocomplete="current-password"
                           required>
                    @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100">Masuk</button>
            </form>

            <p class="small text-body-secondary mt-4 mb-0">
                Belum memiliki akun?
                <a href="{{ route('register') }}">Daftar sebagai Mahasiswa</a>
            </p>
        </div>
    </div>
@endsection
