<?php

namespace App\Providers;

use App\Support\RoleHome;
use Illuminate\Auth\Middleware\RedirectIfAuthenticated;
use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        // Pengguna yang sudah login tidak boleh kembali ke halaman guest;
        // arahkan ke dashboard sesuai role agar tidak terjadi loop redirect.
        RedirectIfAuthenticated::redirectUsing(function (Request $request) {
            $user = $request->user();

            if ($user === null) {
                return '/login';
            }

            return $user->must_change_password
                ? route('account.password.edit')
                : route(RoleHome::routeName($user->role));
        });
    }
}
