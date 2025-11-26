<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Enums\RefundStatus;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Services\RefundService;
use TapPay\Tap\Tests\TestCase;

class RefundServiceTest extends TestCase
{
    protected RefundService $refundService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->refundService = new RefundService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_create_a_full_refund_successfully(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'ref_test_123456',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'INITIATED',
            'charge_id' => 'chg_test_789',
            'reason' => 'Customer requested refund',
        ])));

        $refund = $this->refundService->create([
            'charge_id' => 'chg_test_789',
            'amount' => 10.50,
            'currency' => 'USD',
            'reason' => 'Customer requested refund',
        ]);

        $this->assertSame('ref_test_123456', $refund->id());
        $this->assertSame(10.50, $refund->amount());
        $this->assertSame('USD', $refund->currency());
        $this->assertSame(RefundStatus::INITIATED, $refund->status());
        $this->assertInstanceOf(RefundStatus::class, $refund->status());
        $this->assertSame('chg_test_789', $refund->chargeId());
        $this->assertSame('Customer requested refund', $refund->reason());
        $this->assertTrue($refund->isPending());
        $this->assertFalse($refund->isSuccessful());
        $this->assertFalse($refund->hasFailed());
        $this->assertSame('Initiated', $refund->status()->label());
    }

    #[Test]
    public function it_can_create_a_partial_refund(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'ref_test_partial',
            'amount' => 5.25,
            'currency' => 'USD',
            'status' => 'PENDING',
            'charge_id' => 'chg_test_789',
            'reason' => 'Partial refund requested',
            'metadata' => [
                'order_id' => 'ORD-12345',
                'refund_type' => 'partial',
            ],
        ])));

        $refund = $this->refundService->create([
            'charge_id' => 'chg_test_789',
            'amount' => 5.25,
            'currency' => 'USD',
            'reason' => 'Partial refund requested',
            'metadata' => [
                'order_id' => 'ORD-12345',
                'refund_type' => 'partial',
            ],
        ]);

        $this->assertSame('ref_test_partial', $refund->id());
        $this->assertSame(5.25, $refund->amount());
        $this->assertSame(RefundStatus::PENDING, $refund->status());
        $this->assertTrue($refund->isPending());
        $this->assertFalse($refund->isSuccessful());
        $this->assertSame('Pending', $refund->status()->label());
        $this->assertIsArray($refund->metadata());
        $this->assertArrayHasKey('order_id', $refund->metadata());
        $this->assertSame('ORD-12345', $refund->metadata()['order_id']);
    }

    #[Test]
    public function it_can_retrieve_a_refund_by_id(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'ref_test_123456',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'SUCCEEDED',
            'charge_id' => 'chg_test_789',
        ])));

        $refund = $this->refundService->retrieve('ref_test_123456');

        $this->assertSame('ref_test_123456', $refund->id());
        $this->assertSame(RefundStatus::SUCCEEDED, $refund->status());
        $this->assertTrue($refund->isSuccessful());
        $this->assertFalse($refund->isPending());
        $this->assertFalse($refund->hasFailed());
        $this->assertSame('Succeeded', $refund->status()->label());
    }

    #[Test]
    public function it_can_update_a_refund(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'ref_test_123456',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'SUCCEEDED',
            'charge_id' => 'chg_test_789',
            'reason' => 'Updated refund reason',
            'metadata' => [
                'updated' => true,
            ],
        ])));

        $refund = $this->refundService->update('ref_test_123456', [
            'reason' => 'Updated refund reason',
            'metadata' => [
                'updated' => true,
            ],
        ]);

        $this->assertSame('ref_test_123456', $refund->id());
        $this->assertSame('Updated refund reason', $refund->reason());
        $this->assertArrayHasKey('updated', $refund->metadata());
        $this->assertTrue($refund->metadata()['updated']);
    }

    #[Test]
    public function it_can_list_refunds(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'refunds' => [
                [
                    'id' => 'ref_test_1',
                    'amount' => 10.00,
                    'currency' => 'USD',
                    'status' => 'SUCCEEDED',
                    'charge_id' => 'chg_test_1',
                ],
                [
                    'id' => 'ref_test_2',
                    'amount' => 20.00,
                    'currency' => 'KWD',
                    'status' => 'PENDING',
                    'charge_id' => 'chg_test_2',
                ],
                [
                    'id' => 'ref_test_3',
                    'amount' => 15.00,
                    'currency' => 'SAR',
                    'status' => 'FAILED',
                    'charge_id' => 'chg_test_3',
                ],
            ],
        ])));

        $refunds = $this->refundService->list(['limit' => 10]);

        $this->assertCount(3, $refunds);
        $this->assertSame('ref_test_1', $refunds[0]->id());
        $this->assertSame('ref_test_2', $refunds[1]->id());
        $this->assertSame('ref_test_3', $refunds[2]->id());
        $this->assertTrue($refunds[0]->isSuccessful());
        $this->assertTrue($refunds[1]->isPending());
        $this->assertTrue($refunds[2]->hasFailed());
    }

    #[Test]
    public function it_handles_empty_refund_list(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'refunds' => [],
        ])));

        $refunds = $this->refundService->list([]);

        $this->assertCount(0, $refunds);
        $this->assertIsArray($refunds);
    }

    #[Test]
    public function it_handles_cancelled_refund_status(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'ref_test_cancelled',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CANCELLED',
            'charge_id' => 'chg_test_789',
        ])));

        $refund = $this->refundService->retrieve('ref_test_cancelled');

        $this->assertSame(RefundStatus::CANCELLED, $refund->status());
        $this->assertTrue($refund->hasFailed());
        $this->assertFalse($refund->isSuccessful());
        $this->assertFalse($refund->isPending());
        $this->assertSame('Cancelled', $refund->status()->label());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->refundService->create([
            'charge_id' => 'chg_test_789',
            'amount' => 10.50,
        ]);
    }

    #[Test]
    public function it_throws_invalid_request_exception_on_422(): void
    {
        $this->mockHandler->append(new Response(422, [], json_encode([
            'message' => 'Invalid charge ID',
            'errors' => ['charge_id' => ['The charge ID is invalid']],
        ])));

        $this->expectException(InvalidRequestException::class);

        $this->refundService->create([
            'charge_id' => 'invalid_charge',
            'amount' => 10.50,
        ]);
    }

    #[Test]
    public function it_throws_api_error_exception_on_400(): void
    {
        $this->mockHandler->append(new Response(400, [], json_encode([
            'message' => 'Refund amount exceeds charge amount',
            'errors' => ['amount' => ['The refund amount cannot exceed the charge amount']],
        ])));

        try {
            $this->refundService->create([
                'charge_id' => 'chg_test_789',
                'amount' => 1000.00,
            ]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Refund amount exceeds charge amount', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
            $this->assertTrue($e->hasErrors());
            $this->assertSame('The refund amount cannot exceed the charge amount', $e->getFirstError());
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

        $this->refundService->create([
            'charge_id' => 'chg_test_789',
            'amount' => 10.50,
        ]);
    }

    #[Test]
    public function it_handles_refund_without_reason(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'ref_test_no_reason',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'INITIATED',
            'charge_id' => 'chg_test_789',
        ])));

        $refund = $this->refundService->create([
            'charge_id' => 'chg_test_789',
            'amount' => 10.50,
            'currency' => 'USD',
        ]);

        $this->assertNull($refund->reason());
    }

    #[Test]
    public function it_handles_refund_without_metadata(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'ref_test_no_metadata',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'INITIATED',
            'charge_id' => 'chg_test_789',
        ])));

        $refund = $this->refundService->create([
            'charge_id' => 'chg_test_789',
            'amount' => 10.50,
            'currency' => 'USD',
        ]);

        $this->assertIsArray($refund->metadata());
        $this->assertEmpty($refund->metadata());
    }

    #[Test]
    public function it_handles_invalid_refund_status_gracefully(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'ref_test_invalid',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'UNKNOWN_STATUS',
            'charge_id' => 'chg_test_789',
        ])));

        $refund = $this->refundService->retrieve('ref_test_invalid');

        // Should default to FAILED for unknown statuses
        $this->assertSame(RefundStatus::FAILED, $refund->status());
        $this->assertTrue($refund->hasFailed());
    }
}
