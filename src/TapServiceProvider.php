<?php

declare(strict_types=1);

namespace TapPay\Tap;

use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Support\Money;
use TapPay\Tap\Webhooks\WebhookController;

final class TapServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tap.php', 'tap');

        $this->app->singleton(Client::class, function (): Client {
            $secretKey = config('tap.secret_key');

            if (empty($secretKey)) {
                throw new RuntimeException(
                    'Tap secret key is not configured. Please publish and configure the tap.php config file.'
                );
            }

            return new Client($secretKey);
        });

        $this->app->singleton(Tap::class, fn (): Tap => new Tap());

        $this->app->singleton(MoneyContract::class, function (): Money {
            $currency = config('tap.currency', 'SAR');

            return new Money(is_string($currency) ? $currency : 'SAR');
        });

        $this->app->alias(MoneyContract::class, Money::class);
        $this->app->alias(Tap::class, 'tap');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tap.php' => config_path('tap.php'),
            ], 'tap-config');
        }

        Route::post('tap/webhook', WebhookController::class)->name('tap.webhook');
    }
}