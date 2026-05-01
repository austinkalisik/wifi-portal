<?php

namespace App\Providers;

use App\Services\Spotipo\SpotipoClient;
use App\Services\Spotipo\SpotipoProvider;
use App\Services\WifiAccess\LocalWifiAccessProvider;
use App\Services\WifiAccess\WifiAccessProvider;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(SpotipoClient::class, fn (): SpotipoClient => new SpotipoClient(
            config('services.spotipo.base_url'),
            config('services.spotipo.auth_token'),
            config('services.spotipo.timeout', 15),
        ));

        $this->app->singleton(SpotipoProvider::class, fn ($app): SpotipoProvider => new SpotipoProvider(
            $app->make(SpotipoClient::class),
            config('services.spotipo.site_id'),
        ));

        $this->app->bind(WifiAccessProvider::class, function ($app): WifiAccessProvider {
            if (config('services.wifi_access_provider') === 'spotipo') {
                return $app->make(SpotipoProvider::class);
            }

            return new LocalWifiAccessProvider();
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
