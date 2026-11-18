<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        return view('pages.account.show', [
            'user' => $request->user()->load('mahasiswa', 'guruDosen'),
        ]);
    }

    public function editPassword(): View
    {
        return view('pages.account.password');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $request->user()->update([
            'password' => $request->validated('password'),
        ]);

        $request->session()->regenerateToken();

        return redirect()
            ->route('account.password.edit')
            ->with('status', 'Password berhasil diperbarui.');
    }
}
