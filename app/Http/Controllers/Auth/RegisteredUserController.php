<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Services\RegistrationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    public function create(): View
    {
        return view('pages.auth.register');
    }

    public function store(RegisterRequest $request, RegistrationService $registration): RedirectResponse
    {
        $user = $registration->register($request->validated());

        Auth::login($user);

        $request->session()->regenerate();

        return redirect()
            ->route('mahasiswa.dashboard')
            ->with('status', 'Akun mahasiswa berhasil dibuat.');
    }
}
