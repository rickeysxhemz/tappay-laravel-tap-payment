<?php

declare(strict_types=1);

namespace TapPay\Tap;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use TapPay\Tap\Http\Client;

class TapServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Merge package config with user config
        $this->mergeConfigFrom(
            __DIR__ . '/../config/tap.php',
            'tap'
        );

        // Register the HTTP Client as a singleton
        $this->app->singleton(Client::class, function (): Client {
            $secretKey = config('tap.secret_key');

            if (empty($secretKey)) {
                throw new RuntimeException(
                    'Tap secret key is not configured. Please publish and configure the tap.php config file.'
                );
            }

            return new Client($secretKey);
        });

        // Register the main Tap class as a singleton
        $this->app->singleton(Tap::class, function (): Tap {
            return new Tap();
        });

        // Alias for easier resolution
        $this->app->alias(Tap::class, 'tap');
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

        // Load routes (automatically handles route caching)
        $this->loadRoutesFrom(__DIR__ . '/../routes/webhooks.php');
    }
}