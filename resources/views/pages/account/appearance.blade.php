@extends('layouts.app')

@section('title', 'Pengaturan Tampilan')

@section('content')
    <div class="app-card p-4" style="max-width: 640px;">
        <h2 class="h5 mb-3">Tampilan</h2>
        <p class="small text-body-secondary">
            Preferensi ini disimpan sebagai cookie di browser, bukan data akun. Pilihan ini tidak mengubah hak akses atau data aplikasi.
        </p>

        <form method="POST" action="{{ route('account.appearance.update') }}">
            @csrf
            @method('PATCH')

            <fieldset class="mb-3">
                <legend class="form-label fs-6">Tema</legend>

                <div class="form-check mb-2">
                    <input class="form-check-input @error('theme') is-invalid @enderror"
                           type="radio"
                           name="theme"
                           id="theme-light"
                           value="light"
                           @checked(old('theme', $theme) === 'light')>
                    <label class="form-check-label" for="theme-light">Light Mode</label>
                </div>

                <div class="form-check">
                    <input class="form-check-input @error('theme') is-invalid @enderror"
                           type="radio"
                           name="theme"
                           id="theme-dark"
                           value="dark"
                           @checked(old('theme', $theme) === 'dark')>
                    <label class="form-check-label" for="theme-dark">Dark Mode</label>
                </div>

                @error('theme')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </fieldset>

            <button type="submit" class="btn btn-primary">Simpan Tampilan</button>
        </form>
    </div>
@endsection
