<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Registrar el servicio PBG
        $this->app->bind(
            \App\Services\PbgService::class,
            function ($app) {
                return new \App\Services\PbgService($app->make(\App\Models\PbgData::class));
            }
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
