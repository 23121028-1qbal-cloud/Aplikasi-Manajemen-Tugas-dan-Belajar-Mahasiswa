<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class EnsurePasswordIsRotated
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null || ! $user->must_change_password) {
            return $next($request);
        }

        if ($user->temporary_password_expires_at !== null && $user->temporary_password_expires_at->isPast()) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()
                ->route('login')
                ->withErrors(['username' => 'Temporary password sudah kedaluwarsa. Minta Admin menerbitkan password baru.']);
        }

        if ($request->routeIs('account.password.*', 'logout')) {
            return $next($request);
        }

        return redirect()
            ->route('account.password.edit')
            ->with('status', 'Password sementara wajib diganti sebelum melanjutkan.');
    }
}
