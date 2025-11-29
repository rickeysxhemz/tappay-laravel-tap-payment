<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Facades\Http;
use Orchestra\Testbench\TestCase as Orchestra;
use TapPay\Tap\Http\Client;
use TapPay\Tap\TapServiceProvider;

abstract class TestCase extends Orchestra
{
    protected MockHandler $mockHandler;

    protected bool $useRealApi = false;

    protected function setUp(): void
    {
        parent::setUp();

        $this->useRealApi = filter_var(env('TAP_REAL_API_TESTING', false), FILTER_VALIDATE_BOOLEAN);
    }

    /**
     * Create a mocked HTTP client for testing
     */
    protected function mockHttpClient(): Client
    {
        $this->mockHandler = new MockHandler;
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $httpClient = new Client(config('tap.secret'));

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
        $app['config']->set('tap.secret', 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ');
        $app['config']->set('tap.key', 'pk_test_EtHFV4BuPQokJT6jiROls87Y');
        $app['config']->set('tap.currency', 'SAR');
        $app['config']->set('tap.base_url', 'https://api.tap.company/v2/');
        $app['config']->set('tap.webhook.secret', 'test_webhook_secret');
    }

    /**
     * Queue a mock response
     */
    protected function queueMockResponse(array $data, int $statusCode = 200): void
    {
        if (isset($this->mockHandler)) {
            $this->mockHandler->append(new Response($statusCode, [], json_encode($data)));
        }
    }

    /**
     * Mock a Tap API response using Laravel's Http facade
     */
    protected function mockTapApi(string $endpoint, array $response, int $statusCode = 200): void
    {
        Http::fake([
            "api.tap.company/v2/{$endpoint}*" => Http::response($response, $statusCode),
        ]);
    }

    /**
     * Load test fixture
     */
    protected function loadFixture(string $name): array
    {
        $path = __DIR__ . '/Fixtures/' . $name;

        if (! file_exists($path)) {
            throw new \RuntimeException("Fixture file not found: {$path}");
        }

        return json_decode(file_get_contents($path), true);
    }

    /**
     * Create a test customer array
     */
    protected function createTestCustomer(array $overrides = []): array
    {
        return array_merge([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john.doe@example.com',
            'phone' => [
                'country_code' => '965',
                'number' => '50000000',
            ],
        ], $overrides);
    }

    /**
     * Create a test charge data array
     */
    protected function createTestChargeData(array $overrides = []): array
    {
        return array_merge([
            'amount' => 10.5,
            'currency' => 'SAR',
            'source' => ['id' => 'src_card'],
            'customer' => $this->createTestCustomer(),
            'description' => 'Test charge',
        ], $overrides);
    }

    /**
     * Skip test if not using real API
     */
    protected function requiresRealApi(): void
    {
        if (! $this->useRealApi) {
            $this->markTestSkipped('This test requires real API testing to be enabled. Set TAP_REAL_API_TESTING=true in phpunit.xml');
        }
    }
}
