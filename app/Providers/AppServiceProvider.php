<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
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
        // Safety net: even if APP_DEBUG=true is left in a production .env, never show debug pages.
        if ($this->app->environment('production') && config('app.debug')) {
            config(['app.debug' => false]);
        }

        // If the site's address (APP_URL) is https, always build https links for CSS, scripts and
        // forms, even when a tunnel or proxy hides the original https connection from PHP.
        if (str_starts_with(strtolower(trim((string) config('app.url'))), 'https://')) {
            URL::forceScheme('https');
        }
    }
}
