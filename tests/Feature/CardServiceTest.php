<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Services\CardService;
use TapPay\Tap\Tests\TestCase;

class CardServiceTest extends TestCase
{
    protected CardService $cardService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cardService = new CardService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_retrieve_a_card(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'card_test_123456',
            'object' => 'card',
            'customer' => 'cus_test_789',
            'brand' => 'VISA',
            'funding' => 'CREDIT',
            'first_six' => '424242',
            'last_four' => '4242',
            'exp_month' => 12,
            'exp_year' => 2025,
            'name' => 'John Doe',
        ])));

        $card = $this->cardService->retrieve('cus_test_789', 'card_test_123456');

        $this->assertSame('card_test_123456', $card->id());
        $this->assertSame('cus_test_789', $card->customerId());
        $this->assertSame('VISA', $card->brand());
        $this->assertSame('CREDIT', $card->funding());
        $this->assertSame('424242', $card->firstSix());
        $this->assertSame('4242', $card->lastFour());
        $this->assertSame(12, $card->expiryMonth());
        $this->assertSame(2025, $card->expiryYear());
        $this->assertSame('John Doe', $card->name());
    }

    #[Test]
    public function it_can_list_cards_for_customer(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'cards' => [
                [
                    'id' => 'card_test_1',
                    'object' => 'card',
                    'customer' => 'cus_test_789',
                    'brand' => 'VISA',
                    'last_four' => '4242',
                ],
                [
                    'id' => 'card_test_2',
                    'object' => 'card',
                    'customer' => 'cus_test_789',
                    'brand' => 'MASTERCARD',
                    'last_four' => '5555',
                ],
            ],
        ])));

        $cards = $this->cardService->list('cus_test_789');

        $this->assertCount(2, $cards);
        $this->assertSame('card_test_1', $cards[0]->id());
        $this->assertSame('VISA', $cards[0]->brand());
        $this->assertSame('card_test_2', $cards[1]->id());
        $this->assertSame('MASTERCARD', $cards[1]->brand());
    }

    #[Test]
    public function it_can_list_cards_with_params(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'cards' => [
                [
                    'id' => 'card_test_1',
                    'brand' => 'VISA',
                ],
            ],
        ])));

        $cards = $this->cardService->list('cus_test_789', ['limit' => 5]);

        $this->assertCount(1, $cards);
    }

    #[Test]
    public function it_handles_empty_card_list(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'cards' => [],
        ])));

        $cards = $this->cardService->list('cus_test_789');

        $this->assertCount(0, $cards);
        $this->assertIsArray($cards);
    }

    #[Test]
    public function it_can_delete_a_card(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'card_test_123456',
            'deleted' => true,
        ])));

        $this->cardService->delete('cus_test_789', 'card_test_123456');

        $this->assertTrue(true);
    }

    #[Test]
    public function it_can_verify_a_card(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'card_test_verified',
            'object' => 'card',
            'brand' => 'VISA',
            'last_four' => '4242',
            'verified' => true,
        ])));

        $card = $this->cardService->verify([
            'card' => [
                'number' => '4242424242424242',
                'exp_month' => 12,
                'exp_year' => 2025,
                'cvc' => '123',
            ],
        ]);

        $this->assertSame('card_test_verified', $card->id());
        $this->assertSame('VISA', $card->brand());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->cardService->retrieve('cus_test_789', 'card_test_123');
    }

    #[Test]
    public function it_throws_api_error_exception_on_404(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Card not found',
        ])));

        try {
            $this->cardService->retrieve('cus_test_789', 'card_nonexistent');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Card not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_throws_exception_when_deleting_nonexistent_card(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Card not found',
        ])));

        $this->expectException(ApiErrorException::class);

        $this->cardService->delete('cus_test_789', 'card_nonexistent');
    }
}
