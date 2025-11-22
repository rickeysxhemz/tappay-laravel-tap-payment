<?php

declare(strict_types=1);

namespace TapPay\Tap;

use Illuminate\Support\ServiceProvider;
use TapPay\Tap\Http\Client;

class TapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tap.php', 'tap');

        // Register the HTTP Client as a singleton
        $this->app->singleton(Client::class, function ($app) {
            return new Client(config('tap.secret_key'));
        });

        // Register the main Tap class as a singleton
        $this->app->singleton('tap', function ($app) {
            return new Tap();
        });

        // Bind Tap class for dependency injection
        $this->app->singleton(Tap::class, function ($app) {
            return $app->make('tap');
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            // Publish configuration file
            $this->publishes([
                __DIR__ . '/../config/tap.php' => config_path('tap.php'),
            ], 'tap-config');
        }

        // Load routes for webhooks
        $this->loadRoutesFrom(__DIR__ . '/../routes/webhooks.php');
    }
}
