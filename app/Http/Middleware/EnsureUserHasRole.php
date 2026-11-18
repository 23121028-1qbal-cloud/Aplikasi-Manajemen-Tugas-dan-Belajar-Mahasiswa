<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Role middleware hanya untuk pembatasan tingkat umum.
     * Otorisasi resource tetap dilakukan policy pada fase terkait.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if ($user === null) {
            return redirect()->route('login');
        }

        abort_unless($user->hasRole(...$roles), 403, 'Anda tidak memiliki akses ke halaman ini.');

        return $next($request);
    }
}
