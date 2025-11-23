<?php

declare(strict_types=1);

namespace TapPay\Tap;

use Illuminate\Support\ServiceProvider;
use RuntimeException;
use TapPay\Tap\Http\Client;

/**
 * Tap Payments Laravel Service Provider
 *
 * Registers and bootstraps the Tap Payments SDK services within Laravel.
 * This provider handles service binding, configuration publishing, and route registration.
 *
 * @package TapPay\Tap
 */
class TapServiceProvider extends ServiceProvider
{
    /**
     * Register services into the container
     *
     * This method is called during the registration phase of the service container.
     * All bindings should be registered here. Do not access other services or configurations
     * that may not yet be available.
     *
     * Registers:
     * - HTTP Client as singleton (with API key validation)
     * - Main Tap class as singleton
     * - 'tap' alias for facade support
     *
     * @return void
     * @throws RuntimeException If secret key is not configured
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
     * Bootstrap services
     *
     * This method is called after all services have been registered.
     * You may access other services and configurations here.
     *
     * Bootstraps:
     * - Configuration file publishing (console only)
     * - Webhook routes registration (always)
     *
     * @return void
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