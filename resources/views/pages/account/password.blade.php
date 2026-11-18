@extends('layouts.app')

@section('title', 'Ubah Password')

@section('content')
    <div class="app-card p-4">
        <h2 class="h5 mb-3">Ubah Password</h2>

        <form method="POST" action="{{ route('account.password.update') }}" class="mw-100" style="max-width: 480px;">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="current_password" class="form-label">Password Saat Ini</label>
                <input type="password"
                       id="current_password"
                       name="current_password"
                       class="form-control @error('current_password') is-invalid @enderror"
                       autocomplete="current-password"
                       required>
                @error('current_password')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password Baru</label>
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
                <label for="password_confirmation" class="form-label">Ulangi Password Baru</label>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="form-control"
                       autocomplete="new-password"
                       minlength="8"
                       required>
            </div>

            <div class="d-flex gap-2">
                <button type="submit" class="btn btn-primary">Simpan</button>
                <a href="{{ route('account.show') }}" class="btn btn-outline-secondary">Batal</a>
            </div>
        </form>
    </div>
@endsection
