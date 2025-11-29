<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Enums\ChargeStatus;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Tests\TestCase;

class ChargeServiceTest extends TestCase
{
    protected ChargeService $chargeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->chargeService = new ChargeService(
            $this->mockHttpClient(),
            app(MoneyContract::class)
        );
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
        $this->assertSame(10.50, $charge->amount()->toDecimal());
        $this->assertSame('USD', $charge->currency());
        $this->assertSame(ChargeStatus::INITIATED, $charge->status());
        $this->assertInstanceOf(ChargeStatus::class, $charge->status());
        $this->assertTrue($charge->isPending());
        $this->assertFalse($charge->isSuccessful());
        $this->assertFalse($charge->hasFailed());
        $this->assertSame('Initiated', $charge->status()->label());
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
        $this->assertSame(ChargeStatus::CAPTURED, $charge->status());
        $this->assertTrue($charge->isSuccessful());
        $this->assertFalse($charge->isPending());
        $this->assertFalse($charge->hasFailed());
        $this->assertSame('Captured', $charge->status()->label());
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

        try {
            $this->chargeService->create(['amount' => 0.01]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Invalid amount', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
            $this->assertTrue($e->hasErrors());
            $this->assertSame('The amount must be greater than 0.1', $e->getFirstError());
            $this->assertIsArray($e->toArray());
            $this->assertArrayHasKey('message', $e->toArray());
            $this->assertArrayHasKey('status_code', $e->toArray());
            $this->assertArrayHasKey('errors', $e->toArray());
        }
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

    #[Test]
    public function it_can_update_a_charge(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_test_update',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'metadata' => [
                'order_id' => 'ORD-12345',
                'updated' => true,
            ],
        ])));

        $charge = $this->chargeService->update('chg_test_update', [
            'metadata' => [
                'order_id' => 'ORD-12345',
                'updated' => true,
            ],
        ]);

        $this->assertSame('chg_test_update', $charge->id());
        $this->assertSame(ChargeStatus::CAPTURED, $charge->status());
        $this->assertIsArray($charge->metadata());
        $this->assertArrayHasKey('order_id', $charge->metadata());
        $this->assertArrayHasKey('updated', $charge->metadata());
        $this->assertSame('ORD-12345', $charge->metadata()['order_id']);
        $this->assertTrue($charge->metadata()['updated']);
    }

    #[Test]
    public function it_throws_exception_when_updating_with_invalid_charge_id(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Charge not found',
        ])));

        try {
            $this->chargeService->update('invalid_charge_id', [
                'metadata' => ['key' => 'value'],
            ]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Charge not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_throws_exception_when_updating_with_invalid_data(): void
    {
        $this->mockHandler->append(new Response(400, [], json_encode([
            'message' => 'Invalid update data',
            'errors' => ['metadata' => ['Invalid metadata format']],
        ])));

        try {
            $this->chargeService->update('chg_test_123', [
                'metadata' => 'invalid_metadata',
            ]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Invalid update data', $e->getMessage());
            $this->assertTrue($e->hasErrors());
        }
    }
}
