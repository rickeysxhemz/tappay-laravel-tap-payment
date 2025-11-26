<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use RuntimeException;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Tap;
use TapPay\Tap\Tests\TestCase;

class TapServiceProviderTest extends TestCase
{
    #[Test]
    public function it_registers_http_client_as_singleton(): void
    {
        $client1 = $this->app->make(Client::class);
        $client2 = $this->app->make(Client::class);

        $this->assertInstanceOf(Client::class, $client1);
        $this->assertSame($client1, $client2);
    }

    #[Test]
    public function it_registers_tap_class_as_singleton(): void
    {
        $tap1 = $this->app->make(Tap::class);
        $tap2 = $this->app->make(Tap::class);

        $this->assertInstanceOf(Tap::class, $tap1);
        $this->assertSame($tap1, $tap2);
    }

    #[Test]
    public function it_registers_tap_alias(): void
    {
        $tap = $this->app->make('tap');

        $this->assertInstanceOf(Tap::class, $tap);
    }

    #[Test]
    public function it_resolves_same_instance_for_tap_class_and_alias(): void
    {
        $tapClass = $this->app->make(Tap::class);
        $tapAlias = $this->app->make('tap');

        $this->assertSame($tapClass, $tapAlias);
    }

    #[Test]
    public function it_throws_exception_when_secret_key_is_not_configured(): void
    {
        $this->app['config']->set('tap.secret_key', '');

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tap secret key is not configured');

        $this->app->make(Client::class);
    }

    #[Test]
    public function it_throws_exception_when_secret_key_is_null(): void
    {
        $this->app['config']->set('tap.secret_key', null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Tap secret key is not configured');

        $this->app->make(Client::class);
    }

    #[Test]
    public function it_merges_config_from_package(): void
    {
        $this->assertNotNull(config('tap.secret_key'));
        $this->assertNotNull(config('tap.publishable_key'));
        $this->assertNotNull(config('tap.currency'));
        $this->assertNotNull(config('tap.base_url'));
    }

    #[Test]
    public function it_uses_user_config_over_package_config(): void
    {
        $this->app['config']->set('tap.currency', 'KWD');

        $this->assertSame('KWD', config('tap.currency'));
    }

    #[Test]
    public function it_creates_http_client_with_configured_secret_key(): void
    {
        $this->app['config']->set('tap.secret_key', 'sk_test_custom_key');

        $client = $this->app->make(Client::class);

        $this->assertInstanceOf(Client::class, $client);
    }

    #[Test]
    public function it_loads_webhook_routes(): void
    {
        $routes = $this->app['router']->getRoutes();

        $hasWebhookRoute = false;
        foreach ($routes as $route) {
            if (str_contains($route->uri(), 'tap/webhook')) {
                $hasWebhookRoute = true;

                break;
            }
        }

        $this->assertTrue($hasWebhookRoute, 'Webhook route should be registered');
    }

    #[Test]
    public function it_provides_tap_facade(): void
    {
        $facade = \Tap::getFacadeRoot();

        $this->assertInstanceOf(Tap::class, $facade);
    }

    #[Test]
    public function it_can_access_services_through_tap_instance(): void
    {
        $tap = $this->app->make(Tap::class);

        $this->assertIsObject($tap->charges());
        $this->assertIsObject($tap->customers());
        $this->assertIsObject($tap->refunds());
        $this->assertIsObject($tap->authorizations());
        $this->assertIsObject($tap->tokens());
    }
}
