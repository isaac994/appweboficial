<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Auth;
use App\Auth\DefaultAdminUserProvider;

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
        // Registrar el User Provider personalizado para el guard 'web'
        Auth::provider('default_admin', function ($app, array $config) {
            return new DefaultAdminUserProvider(
                $app['hash'],
                $config['model']
            );
        });
    }
}
