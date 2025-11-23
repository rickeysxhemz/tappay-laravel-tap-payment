<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use Orchestra\Testbench\TestCase as Orchestra;
use TapPay\Tap\Http\Client;
use TapPay\Tap\TapServiceProvider;

abstract class TestCase extends Orchestra
{
    protected MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();
    }

    /**
     * Create a mocked HTTP client for testing
     *
     * @return Client
     */
    protected function mockHttpClient(): Client
    {
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $httpClient = new Client(config('tap.secret_key'));

        // Use reflection to inject mocked Guzzle client
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $guzzleClient);

        return $httpClient;
    }

    /**
     * Get package providers
     */
    protected function getPackageProviders($app): array
    {
        return [
            TapServiceProvider::class,
        ];
    }

    /**
     * Get package aliases
     */
    protected function getPackageAliases($app): array
    {
        return [
            'Tap' => \TapPay\Tap\Facades\Tap::class,
        ];
    }

    /**
     * Define environment setup
     */
    protected function defineEnvironment($app): void
    {
        $app['config']->set('tap.secret_key', 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ');
        $app['config']->set('tap.publishable_key', 'pk_test_EtHFV4BuPQokJT6jiROls87Y');
        $app['config']->set('tap.currency', 'USD');
        $app['config']->set('tap.base_url', 'https://api.tap.company/v2/');
    }
}
