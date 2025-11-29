<?php

declare(strict_types=1);

namespace TapPay\Tap;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Support\Money;

final class TapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tap.php', 'tap');

        $this->app->singleton(Client::class, function (): Client {
            $secret = config('tap.secret');

            if (empty($secret)) {
                throw new RuntimeException(
                    'Tap secret key is not configured. Set the TAP_SECRET environment variable or publish and configure the tap.php config file using: php artisan vendor:publish --tag=tap-config'
                );
            }

            return new Client($secret);
        });

        $this->app->singleton(MoneyContract::class, function (): Money {
            $currency = config('tap.currency', 'SAR');

            return new Money(is_string($currency) ? $currency : 'SAR');
        });

        $this->app->singleton(Tap::class, fn (): Tap => new Tap(
            $this->app->make(Client::class),
            $this->app->make(MoneyContract::class)
        ));

        $this->app->alias(MoneyContract::class, Money::class);
        $this->app->alias(Tap::class, 'tap');
    }

    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerRoutes();
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tap.php' => config_path('tap.php'),
            ], 'tap-config');
        }
    }

    protected function registerRoutes(): void
    {
        if (Tap::registersRoutes()) {
            Route::group([
                'prefix' => config('tap.path', 'tap'),
                'as' => 'tap.',
            ], function (): void {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
        }
    }
}