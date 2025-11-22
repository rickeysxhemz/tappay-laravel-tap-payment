<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use TapPay\Tap\Enums\ChargeStatus;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Tests\TestCase;

class ChargeServiceTest extends TestCase
{
    protected ChargeService $chargeService;
    protected MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a mock handler for Guzzle
        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        // Create HTTP client with mocked Guzzle client
        $httpClient = new Client(config('tap.secret_key'));

        // Use reflection to inject mocked Guzzle client
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $guzzleClient);

        $this->chargeService = new ChargeService($httpClient);
    }
    #[Test]
    public function it_can_create_a_charge_successfully(): void
    {
        // Mock successful API response
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_test_123456',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'INITIATED',
            'transaction' => [
                'url' => 'https://tap.company/redirect/123',
            ],
            'customer' => [
                'id' => 'cus_test_789',
            ],
            'source' => [
                'id' => 'src_card',
            ],
        ])));

        $charge = $this->chargeService->create([
            'amount' => 10.50,
            'currency' => 'USD',
            'source' => ['id' => 'src_card'],
            'redirect' => ['url' => 'https://example.com/return'],
        ]);

        $this->assertSame('chg_test_123456', $charge->id());
        $this->assertSame(10.50, $charge->amount());
        $this->assertSame('USD', $charge->currency());
        $this->assertSame(ChargeStatus::INITIATED, $charge->status());
        $this->assertSame('https://tap.company/redirect/123', $charge->transactionUrl());
    }
    #[Test]
    public function it_can_retrieve_a_charge(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_test_123456',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
        ])));

        $charge = $this->chargeService->retrieve('chg_test_123456');

        $this->assertSame('chg_test_123456', $charge->id());
        $this->assertTrue($charge->isSuccessful());
    }
    #[Test]
    public function it_can_list_charges(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'charges' => [
                [
                    'id' => 'chg_test_1',
                    'amount' => 10.00,
                    'currency' => 'USD',
                    'status' => 'CAPTURED',
                ],
                [
                    'id' => 'chg_test_2',
                    'amount' => 20.00,
                    'currency' => 'KWD',
                    'status' => 'INITIATED',
                ],
            ],
        ])));

        $charges = $this->chargeService->list(['limit' => 10]);

        $this->assertCount(2, $charges);
        $this->assertSame('chg_test_1', $charges[0]->id());
        $this->assertSame('chg_test_2', $charges[1]->id());
    }
    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->chargeService->create(['amount' => 10]);
    }
    #[Test]
    public function it_throws_api_error_exception_on_400(): void
    {
        $this->mockHandler->append(new Response(400, [], json_encode([
            'message' => 'Invalid amount',
            'errors' => ['amount' => ['The amount must be greater than 0.1']],
        ])));

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Invalid amount');

        $this->chargeService->create(['amount' => 0.01]);
    }
    #[Test]
    public function it_handles_server_errors(): void
    {
        $this->mockHandler->append(new Response(500, [], json_encode([
            'message' => 'Internal Server Error',
        ])));

        $this->expectException(ApiErrorException::class);

        $this->chargeService->create(['amount' => 10]);
    }
}