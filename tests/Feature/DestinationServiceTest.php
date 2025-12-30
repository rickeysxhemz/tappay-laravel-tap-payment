<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Services\DestinationService;
use TapPay\Tap\Tests\TestCase;

class DestinationServiceTest extends TestCase
{
    protected DestinationService $destinationService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->destinationService = new DestinationService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_retrieve_a_destination(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'dest_test_123456',
            'merchant' => 'merchant_test_789',
            'amount' => 50.00,
            'currency' => 'SAR',
            'status' => 'PENDING',
            'charge' => 'chg_test_abc',
        ])));

        $destination = $this->destinationService->retrieve('dest_test_123456');

        $this->assertSame('dest_test_123456', $destination->id());
        $this->assertSame('merchant_test_789', $destination->merchantId());
        $this->assertSame(50.00, $destination->amount()->toDecimal());
        $this->assertSame('SAR', $destination->currency());
        $this->assertSame('PENDING', $destination->status());
        $this->assertTrue($destination->isPending());
        $this->assertFalse($destination->isComplete());
        $this->assertSame('chg_test_abc', $destination->chargeId());
    }

    #[Test]
    public function it_can_list_destinations(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'destinations' => [
                [
                    'id' => 'dest_test_1',
                    'merchant' => 'merchant_test_1',
                    'amount' => 30.00,
                    'currency' => 'SAR',
                    'status' => 'TRANSFERRED',
                ],
                [
                    'id' => 'dest_test_2',
                    'merchant' => 'merchant_test_2',
                    'amount' => 20.00,
                    'currency' => 'SAR',
                    'status' => 'PENDING',
                ],
            ],
        ])));

        $destinations = $this->destinationService->list(['limit' => 10]);

        $this->assertCount(2, $destinations);
        $this->assertSame('dest_test_1', $destinations[0]->id());
        $this->assertTrue($destinations[0]->isComplete());
        $this->assertSame('dest_test_2', $destinations[1]->id());
        $this->assertTrue($destinations[1]->isPending());
    }

    #[Test]
    public function it_can_list_destinations_by_merchant(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'destinations' => [
                [
                    'id' => 'dest_test_1',
                    'merchant' => 'merchant_test_123',
                    'amount' => 100.00,
                    'currency' => 'SAR',
                    'status' => 'TRANSFERRED',
                ],
            ],
        ])));

        $destinations = $this->destinationService->listByMerchant('merchant_test_123');

        $this->assertCount(1, $destinations);
        $this->assertSame('merchant_test_123', $destinations[0]->merchantId());
    }

    #[Test]
    public function it_can_list_destinations_by_charge(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'destinations' => [
                [
                    'id' => 'dest_test_1',
                    'merchant' => 'merchant_test_1',
                    'amount' => 70.00,
                    'currency' => 'SAR',
                    'charge' => 'chg_test_abc',
                    'status' => 'PENDING',
                ],
                [
                    'id' => 'dest_test_2',
                    'merchant' => 'merchant_test_2',
                    'amount' => 30.00,
                    'currency' => 'SAR',
                    'charge' => 'chg_test_abc',
                    'status' => 'PENDING',
                ],
            ],
        ])));

        $destinations = $this->destinationService->listByCharge('chg_test_abc');

        $this->assertCount(2, $destinations);
        $this->assertSame('chg_test_abc', $destinations[0]->chargeId());
        $this->assertSame('chg_test_abc', $destinations[1]->chargeId());
    }

    #[Test]
    public function it_handles_empty_destination_list(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'destinations' => [],
        ])));

        $destinations = $this->destinationService->list([]);

        $this->assertCount(0, $destinations);
    }

    #[Test]
    public function it_handles_destination_with_transfer_id(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'dest_test_settled',
            'merchant' => 'merchant_test_789',
            'amount' => 50.00,
            'currency' => 'SAR',
            'status' => 'TRANSFERRED',
            'transfer' => 'tr_test_123456',
        ])));

        $destination = $this->destinationService->retrieve('dest_test_settled');

        $this->assertTrue($destination->isComplete());
        $this->assertSame('tr_test_123456', $destination->transferId());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->destinationService->retrieve('dest_test_123');
    }

    #[Test]
    public function it_throws_api_error_exception_on_404(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Destination not found',
        ])));

        try {
            $this->destinationService->retrieve('dest_nonexistent');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Destination not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }
}
