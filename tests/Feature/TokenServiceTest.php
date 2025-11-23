<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Psr7\Response;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Services\TokenService;
use TapPay\Tap\Tests\TestCase;

class TokenServiceTest extends TestCase
{
    protected TokenService $tokenService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tokenService = new TokenService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_create_a_token_successfully(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'tok_test_123456',
            'card' => 'card_test_789',
            'customer' => 'cus_test_456',
            'created' => 1640000000,
        ])));

        $token = $this->tokenService->create([
            'card' => 'card_test_789',
            'customer' => 'cus_test_456',
        ]);

        $this->assertSame('tok_test_123456', $token->id());
        $this->assertSame('card_test_789', $token->cardId());
        $this->assertSame('cus_test_456', $token->customerId());
        $this->assertSame(1640000000, $token->created());
    }

    #[Test]
    public function it_can_create_token_with_minimal_data(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'tok_test_minimal',
            'card' => 'card_test_123',
        ])));

        $token = $this->tokenService->create([
            'card' => 'card_test_123',
        ]);

        $this->assertSame('tok_test_minimal', $token->id());
        $this->assertSame('card_test_123', $token->cardId());
        $this->assertNull($token->customerId());
        $this->assertNull($token->created());
    }

    #[Test]
    public function it_can_retrieve_a_token(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'tok_test_retrieve',
            'card' => 'card_test_retrieve',
            'customer' => 'cus_test_retrieve',
            'created' => 1650000000,
        ])));

        $token = $this->tokenService->retrieve('tok_test_retrieve');

        $this->assertSame('tok_test_retrieve', $token->id());
        $this->assertSame('card_test_retrieve', $token->cardId());
        $this->assertSame('cus_test_retrieve', $token->customerId());
        $this->assertSame(1650000000, $token->created());
    }

    #[Test]
    public function it_handles_token_without_customer(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'tok_no_customer',
            'card' => 'card_test_123',
            'created' => 1640000000,
        ])));

        $token = $this->tokenService->create([
            'card' => 'card_test_123',
        ]);

        $this->assertNull($token->customerId());
    }

    #[Test]
    public function it_handles_token_without_created_timestamp(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'tok_no_timestamp',
            'card' => 'card_test_123',
            'customer' => 'cus_test_456',
        ])));

        $token = $this->tokenService->retrieve('tok_no_timestamp');

        $this->assertNull($token->created());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->tokenService->create([
            'card' => 'card_test_123',
        ]);
    }

    #[Test]
    public function it_throws_invalid_request_exception_on_422(): void
    {
        $this->mockHandler->append(new Response(422, [], json_encode([
            'message' => 'Invalid token data',
            'errors' => ['card' => ['The card field is required']],
        ])));

        $this->expectException(InvalidRequestException::class);

        $this->tokenService->create([]);
    }

    #[Test]
    public function it_throws_api_error_exception_on_400(): void
    {
        $this->mockHandler->append(new Response(400, [], json_encode([
            'message' => 'Invalid card ID',
            'errors' => ['card' => ['The card ID is invalid']],
        ])));

        try {
            $this->tokenService->create([
                'card' => 'invalid_card_id',
            ]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Invalid card ID', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
            $this->assertTrue($e->hasErrors());
            $this->assertSame('The card ID is invalid', $e->getFirstError());
        }
    }

    #[Test]
    public function it_throws_exception_when_card_not_found(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Card not found',
        ])));

        try {
            $this->tokenService->create([
                'card' => 'card_nonexistent',
            ]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Card not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_throws_exception_when_retrieving_invalid_token_id(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Token not found',
        ])));

        try {
            $this->tokenService->retrieve('invalid_token_id');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Token not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_throws_exception_when_customer_does_not_own_card(): void
    {
        $this->mockHandler->append(new Response(400, [], json_encode([
            'message' => 'Card does not belong to customer',
            'errors' => ['customer' => ['The card does not belong to this customer']],
        ])));

        try {
            $this->tokenService->create([
                'card' => 'card_test_123',
                'customer' => 'cus_different_owner',
            ]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Card does not belong to customer', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
            $this->assertTrue($e->hasErrors());
        }
    }

    #[Test]
    public function it_handles_server_errors(): void
    {
        $this->mockHandler->append(new Response(500, [], json_encode([
            'message' => 'Internal Server Error',
        ])));

        $this->expectException(ApiErrorException::class);

        $this->tokenService->create([
            'card' => 'card_test_123',
        ]);
    }

    #[Test]
    public function it_creates_token_for_saved_card_flow(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'tok_saved_card',
            'card' => 'card_saved_123',
            'customer' => 'cus_owner_456',
            'created' => 1660000000,
        ])));

        $token = $this->tokenService->create([
            'card' => 'card_saved_123',
            'customer' => 'cus_owner_456',
        ]);

        $this->assertSame('tok_saved_card', $token->id());
        $this->assertSame('card_saved_123', $token->cardId());
        $this->assertSame('cus_owner_456', $token->customerId());
    }

    #[Test]
    public function it_retrieves_token_details(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'tok_details_test',
            'card' => 'card_visa_ending_4242',
            'customer' => 'cus_john_doe',
            'created' => 1670000000,
        ])));

        $token = $this->tokenService->retrieve('tok_details_test');

        $this->assertSame('tok_details_test', $token->id());
        $this->assertSame('card_visa_ending_4242', $token->cardId());
        $this->assertSame('cus_john_doe', $token->customerId());
        $this->assertSame(1670000000, $token->created());
        $this->assertIsInt($token->created());
    }
}