<?php

declare(strict_types=1);

namespace TapPay\Tap;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use RuntimeException;
use TapPay\Tap\Contracts\ClientResolver;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Contracts\WebhookValidatorResolver;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Http\Handlers\WebhookHandler;
use TapPay\Tap\Http\Handlers\WebhookProcessor;
use TapPay\Tap\Support\ContainerClientResolver;
use TapPay\Tap\Support\ContainerWebhookValidatorResolver;
use TapPay\Tap\Support\Money;
use TapPay\Tap\Webhooks\WebhookValidator;

final class TapServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/tap.php', 'tap');

        $this->registerResolvers();
        $this->registerServices();
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerPublishing();
        $this->registerRoutes();
    }

    /**
     * Register the resolvers.
     */
    protected function registerResolvers(): void
    {
        $this->app->singleton(ClientResolver::class, ContainerClientResolver::class);
        $this->app->singleton(WebhookValidatorResolver::class, ContainerWebhookValidatorResolver::class);
    }

    /**
     * Register the services.
     */
    protected function registerServices(): void
    {
        $this->app->scoped(Client::class, function (): Client {
            $secret = config('tap.secret_key');

            if (! is_string($secret) || $secret === '') {
                throw new RuntimeException(
                    'Tap secret key is not configured. Set the TAP_SECRET_KEY environment variable or publish and configure the tap.php config file using: php artisan vendor:publish --tag=tap-config'
                );
            }

            return new Client($secret);
        });

        $this->app->scoped(MoneyContract::class, function (): Money {
            $currency = config('tap.currency', 'SAR');

            return new Money(is_string($currency) ? $currency : 'SAR');
        });

        $this->app->scoped(Tap::class, function (Application $app): Tap {
            return new Tap(
                $app->make(ClientResolver::class),
                $app->make(MoneyContract::class)
            );
        });

        $this->app->scoped(WebhookValidator::class, function (): WebhookValidator {
            $secret = config('tap.webhook.secret') ?? config('tap.secret_key');

            if (! is_string($secret) || $secret === '') {
                throw new RuntimeException(
                    'Webhook secret key is not configured. Set TAP_WEBHOOK_SECRET or TAP_SECRET_KEY in your .env file.'
                );
            }

            $tolerance = config('tap.webhook.tolerance', 300);

            return new WebhookValidator(
                $secret,
                $this->app->make(MoneyContract::class),
                is_numeric($tolerance) ? (int) $tolerance : 300
            );
        });

        $this->app->scoped(WebhookProcessor::class, function (Application $app): WebhookProcessor {
            return new WebhookProcessor(
                $app->make(WebhookValidatorResolver::class),
                $app->make(WebhookHandler::class)
            );
        });

        $this->app->alias(MoneyContract::class, Money::class);
        $this->app->alias(Tap::class, 'tap');
    }

    /**
     * Register the publishing.
     */
    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/tap.php' => config_path('tap.php'),
            ], 'tap-config');
        }
    }

    /**
     * Register the routes.
     */
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
