<?php

namespace App\Http\Controllers;

use App\Http\Requests\Account\UpdatePasswordRequest;
use App\Support\RoleHome;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function show(Request $request): View
    {
        $this->authorize('view', $request->user());

        return view('pages.account.show', [
            'user' => $request->user()->load('mahasiswa', 'guruDosen'),
        ]);
    }

    public function editPassword(Request $request): View
    {
        $this->authorize('updatePassword', $request->user());

        return view('pages.account.password');
    }

    public function updatePassword(UpdatePasswordRequest $request): RedirectResponse
    {
        $this->authorize('updatePassword', $request->user());

        $request->user()->forceFill([
            'password' => $request->validated('password'),
            'must_change_password' => false,
            'temporary_password_expires_at' => null,
        ])->save();

        $request->session()->regenerateToken();

        return redirect()
            ->route(RoleHome::routeName($request->user()->role))
            ->with('status', 'Password berhasil diperbarui.');
    }
}
