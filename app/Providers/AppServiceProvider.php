<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use App\Models\User;
use App\Services\SupabaseService;
use App\Services\PelajarService;
use App\Services\DashboardService;
use App\Services\NotifikasiService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // 1. SupabaseService — singleton
        $this->app->singleton(SupabaseService::class, function () {
            return new SupabaseService();
        });

        // 2. PelajarService — inject SupabaseService
        $this->app->singleton(PelajarService::class, function ($app) {
            return new PelajarService(
                $app->make(SupabaseService::class)
            );
        });

        // 3. DashboardService — inject SupabaseService & PelajarService
        $this->app->singleton(DashboardService::class, function ($app) {
            return new DashboardService(
                $app->make(SupabaseService::class),
                $app->make(PelajarService::class)
            );
        });
    }

    public function boot(): void
    {
        Gate::define('admin-only', function (User $user) {
            return $user->role === 'admin';
        });

        // Kongsi $bilNotif (bilangan notifikasi tertunggak) ke layout admin
        // supaya badge loceng dalam sidebar sentiasa tunjuk nombor sebenar
        // dari database, bukan sentiasa kosong/tersembunyi macam sebelum ini.
        View::composer('layouts.app', function ($view) {
            $role = session('role', session('peranan'));
            $bilNotif = 0;

            if (in_array($role, ['admin', 'readonly'], true)) {
                try {
                    $bilNotif = app(NotifikasiService::class)->getStats()['jumlah_notif'] ?? 0;
                } catch (\Throwable) {
                    $bilNotif = 0;
                }
            }

            $view->with('bilNotif', $bilNotif);
        });
    }
}
