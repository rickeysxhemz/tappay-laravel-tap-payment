<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Enums\AuthorizeStatus;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Services\AuthorizeService;
use TapPay\Tap\Tests\TestCase;

class AuthorizeServiceTest extends TestCase
{
    protected AuthorizeService $authorizeService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authorizeService = new AuthorizeService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_create_an_authorization_successfully(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_123456',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
            'transaction' => [
                'url' => 'https://tap.company/redirect/auth/123',
            ],
            'customer' => [
                'id' => 'cus_test_789',
            ],
            'source' => [
                'id' => 'src_card',
            ],
        ])));

        $authorization = $this->authorizeService->create([
            'amount' => 50.00,
            'currency' => 'USD',
            'source' => ['id' => 'src_card'],
            'redirect' => ['url' => 'https://example.com/return'],
        ]);

        $this->assertSame('auth_test_123456', $authorization->id());
        $this->assertSame(50.00, $authorization->amount()->toDecimal());
        $this->assertSame('USD', $authorization->currency());
        $this->assertSame(AuthorizeStatus::INITIATED, $authorization->status());
        $this->assertInstanceOf(AuthorizeStatus::class, $authorization->status());
        $this->assertTrue($authorization->isPending());
        $this->assertFalse($authorization->isAuthorized());
        $this->assertFalse($authorization->hasFailed());
        $this->assertSame('Initiated', $authorization->status()->label());
        $this->assertSame('https://tap.company/redirect/auth/123', $authorization->transactionUrl());
        $this->assertSame('cus_test_789', $authorization->customerId());
        $this->assertSame('src_card', $authorization->sourceId());
    }

    #[Test]
    public function it_can_retrieve_an_authorization(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_123456',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'AUTHORIZED',
        ])));

        $authorization = $this->authorizeService->retrieve('auth_test_123456');

        $this->assertSame('auth_test_123456', $authorization->id());
        $this->assertSame(AuthorizeStatus::AUTHORIZED, $authorization->status());
        $this->assertTrue($authorization->isAuthorized());
        $this->assertFalse($authorization->isPending());
        $this->assertFalse($authorization->hasFailed());
        $this->assertSame('Authorized', $authorization->status()->label());
    }

    #[Test]
    public function it_can_update_an_authorization(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_123456',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'AUTHORIZED',
            'metadata' => [
                'updated' => true,
                'order_id' => 'ORD-12345',
            ],
        ])));

        $authorization = $this->authorizeService->update('auth_test_123456', [
            'metadata' => [
                'updated' => true,
                'order_id' => 'ORD-12345',
            ],
        ]);

        $this->assertSame('auth_test_123456', $authorization->id());
        $this->assertIsArray($authorization->metadata());
        $this->assertArrayHasKey('updated', $authorization->metadata());
        $this->assertTrue($authorization->metadata()['updated']);
        $this->assertSame('ORD-12345', $authorization->metadata()['order_id']);
    }

    #[Test]
    public function it_can_list_authorizations(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'authorizations' => [
                [
                    'id' => 'auth_test_1',
                    'amount' => 10.00,
                    'currency' => 'USD',
                    'status' => 'AUTHORIZED',
                ],
                [
                    'id' => 'auth_test_2',
                    'amount' => 20.00,
                    'currency' => 'KWD',
                    'status' => 'INITIATED',
                ],
                [
                    'id' => 'auth_test_3',
                    'amount' => 30.00,
                    'currency' => 'SAR',
                    'status' => 'FAILED',
                ],
            ],
        ])));

        $authorizations = $this->authorizeService->list(['limit' => 10]);

        $this->assertCount(3, $authorizations);
        $this->assertSame('auth_test_1', $authorizations[0]->id());
        $this->assertSame('auth_test_2', $authorizations[1]->id());
        $this->assertSame('auth_test_3', $authorizations[2]->id());
        $this->assertTrue($authorizations[0]->isAuthorized());
        $this->assertTrue($authorizations[1]->isPending());
        $this->assertTrue($authorizations[2]->hasFailed());
    }

    #[Test]
    public function it_handles_empty_authorization_list(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'authorizations' => [],
        ])));

        $authorizations = $this->authorizeService->list([]);

        $this->assertCount(0, $authorizations);
        $this->assertIsArray($authorizations);
    }

    #[Test]
    public function it_handles_captured_authorization_status(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_captured',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'CAPTURED',
        ])));

        $authorization = $this->authorizeService->retrieve('auth_test_captured');

        $this->assertSame(AuthorizeStatus::CAPTURED, $authorization->status());
        $this->assertFalse($authorization->isAuthorized());
        $this->assertFalse($authorization->isPending());
        $this->assertFalse($authorization->hasFailed());
        $this->assertSame('Captured', $authorization->status()->label());
    }

    #[Test]
    public function it_handles_cancelled_authorization_status(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_cancelled',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'CANCELLED',
        ])));

        $authorization = $this->authorizeService->retrieve('auth_test_cancelled');

        $this->assertSame(AuthorizeStatus::CANCELLED, $authorization->status());
        $this->assertTrue($authorization->hasFailed());
        $this->assertFalse($authorization->isAuthorized());
        $this->assertFalse($authorization->isPending());
        $this->assertSame('Cancelled', $authorization->status()->label());
    }

    #[Test]
    public function it_handles_declined_authorization_status(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_declined',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'DECLINED',
        ])));

        $authorization = $this->authorizeService->retrieve('auth_test_declined');

        $this->assertSame(AuthorizeStatus::DECLINED, $authorization->status());
        $this->assertTrue($authorization->hasFailed());
        $this->assertSame('Declined', $authorization->status()->label());
    }

    #[Test]
    public function it_handles_restricted_authorization_status(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_restricted',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'RESTRICTED',
        ])));

        $authorization = $this->authorizeService->retrieve('auth_test_restricted');

        $this->assertSame(AuthorizeStatus::RESTRICTED, $authorization->status());
        $this->assertTrue($authorization->hasFailed());
        $this->assertSame('Restricted', $authorization->status()->label());
    }

    #[Test]
    public function it_handles_void_authorization_status(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_void',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'VOID',
        ])));

        $authorization = $this->authorizeService->retrieve('auth_test_void');

        $this->assertSame(AuthorizeStatus::VOID, $authorization->status());
        $this->assertTrue($authorization->hasFailed());
        $this->assertSame('Void', $authorization->status()->label());
    }

    #[Test]
    public function it_throws_exception_for_unknown_authorization_status(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_unknown',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'INVALID_STATUS',
        ])));

        $authorization = $this->authorizeService->retrieve('auth_test_unknown');

        $this->expectException(\TapPay\Tap\Exceptions\InvalidStatusException::class);
        $this->expectExceptionMessage("Unknown authorize status: 'INVALID_STATUS'");
        $authorization->status();
    }

    #[Test]
    public function it_handles_authorization_without_transaction_url(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_no_url',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'AUTHORIZED',
        ])));

        $authorization = $this->authorizeService->create([
            'amount' => 50.00,
            'currency' => 'USD',
        ]);

        $this->assertNull($authorization->transactionUrl());
    }

    #[Test]
    public function it_handles_authorization_without_customer(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_no_customer',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
        ])));

        $authorization = $this->authorizeService->create([
            'amount' => 50.00,
            'currency' => 'USD',
        ]);

        $this->assertNull($authorization->customerId());
    }

    #[Test]
    public function it_handles_authorization_without_source(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_no_source',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
        ])));

        $authorization = $this->authorizeService->create([
            'amount' => 50.00,
            'currency' => 'USD',
        ]);

        $this->assertNull($authorization->sourceId());
    }

    #[Test]
    public function it_handles_authorization_without_metadata(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'auth_test_no_metadata',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'AUTHORIZED',
        ])));

        $authorization = $this->authorizeService->retrieve('auth_test_no_metadata');

        $this->assertIsArray($authorization->metadata());
        $this->assertEmpty($authorization->metadata());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->authorizeService->create([
            'amount' => 50.00,
        ]);
    }

    #[Test]
    public function it_throws_invalid_request_exception_on_422(): void
    {
        $this->mockHandler->append(new Response(422, [], json_encode([
            'message' => 'Invalid authorization ID',
            'errors' => ['auth_id' => ['The authorization ID is invalid']],
        ])));

        $this->expectException(InvalidRequestException::class);

        $this->authorizeService->retrieve('invalid_auth');
    }

    #[Test]
    public function it_throws_api_error_exception_on_400(): void
    {
        $this->mockHandler->append(new Response(400, [], json_encode([
            'message' => 'Invalid amount',
            'errors' => ['amount' => ['The amount must be greater than 0.1']],
        ])));

        try {
            $this->authorizeService->create([
                'amount' => 0.01,
            ]);
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

        $this->authorizeService->create([
            'amount' => 50.00,
        ]);
    }
}
