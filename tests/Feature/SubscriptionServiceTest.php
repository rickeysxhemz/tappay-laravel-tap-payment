<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Services\SubscriptionService;
use TapPay\Tap\Tests\TestCase;

class SubscriptionServiceTest extends TestCase
{
    protected SubscriptionService $subscriptionService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->subscriptionService = new SubscriptionService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_create_a_subscription(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'sub_test_123456',
            'amount' => 99.99,
            'currency' => 'SAR',
            'status' => 'ACTIVE',
            'customer' => ['id' => 'cus_test_789'],
            'term' => [
                'interval' => 'MONTHLY',
                'period' => 1,
            ],
            'trial' => [
                'days' => 14,
            ],
        ])));

        $subscription = $this->subscriptionService->create([
            'amount' => 99.99,
            'currency' => 'SAR',
            'customer' => 'cus_test_789',
            'interval' => 'MONTHLY',
        ]);

        $this->assertSame('sub_test_123456', $subscription->id());
        $this->assertSame(99.99, $subscription->amount()->toDecimal());
        $this->assertSame('SAR', $subscription->currency());
        $this->assertTrue($subscription->isActive());
        $this->assertSame('cus_test_789', $subscription->customerId());
        $this->assertSame(14, $subscription->trialDays());
    }

    #[Test]
    public function it_can_retrieve_a_subscription(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'sub_test_123456',
            'amount' => 49.99,
            'currency' => 'SAR',
            'status' => 'ACTIVE',
            'interval' => 'MONTHLY',
            'period' => 1,
        ])));

        $subscription = $this->subscriptionService->retrieve('sub_test_123456');

        $this->assertSame('sub_test_123456', $subscription->id());
        $this->assertTrue($subscription->isActive());
    }

    #[Test]
    public function it_can_update_a_subscription(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'sub_test_123456',
            'amount' => 149.99,
            'currency' => 'SAR',
            'status' => 'ACTIVE',
            'metadata' => ['plan' => 'premium'],
        ])));

        $subscription = $this->subscriptionService->update('sub_test_123456', [
            'amount' => 149.99,
            'metadata' => ['plan' => 'premium'],
        ]);

        $this->assertSame(149.99, $subscription->amount()->toDecimal());
        $this->assertArrayHasKey('plan', $subscription->metadata());
    }

    #[Test]
    public function it_can_list_subscriptions(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'subscriptions' => [
                [
                    'id' => 'sub_test_1',
                    'amount' => 99.99,
                    'currency' => 'SAR',
                    'status' => 'ACTIVE',
                ],
                [
                    'id' => 'sub_test_2',
                    'amount' => 199.99,
                    'currency' => 'SAR',
                    'status' => 'PAUSED',
                ],
            ],
        ])));

        $subscriptions = $this->subscriptionService->list(['limit' => 10]);

        $this->assertCount(2, $subscriptions);
        $this->assertSame('sub_test_1', $subscriptions[0]->id());
        $this->assertTrue($subscriptions[0]->isActive());
        $this->assertSame('sub_test_2', $subscriptions[1]->id());
        $this->assertTrue($subscriptions[1]->isPaused());
    }

    #[Test]
    public function it_handles_empty_subscription_list(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'subscriptions' => [],
        ])));

        $subscriptions = $this->subscriptionService->list([]);

        $this->assertCount(0, $subscriptions);
    }

    #[Test]
    public function it_can_cancel_a_subscription(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'sub_test_123456',
            'amount' => 99.99,
            'currency' => 'SAR',
            'status' => 'CANCELLED',
            'cancelled_at' => '2024-01-15T10:30:00Z',
        ])));

        $subscription = $this->subscriptionService->cancel('sub_test_123456');

        $this->assertSame('sub_test_123456', $subscription->id());
        $this->assertTrue($subscription->isCancelled());
    }

    #[Test]
    public function it_handles_trialing_subscription(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'sub_test_trial',
            'amount' => 99.99,
            'currency' => 'SAR',
            'status' => 'TRIALING',
            'trial' => [
                'days' => 14,
            ],
        ])));

        $subscription = $this->subscriptionService->retrieve('sub_test_trial');

        $this->assertTrue($subscription->isTrialing());
        $this->assertTrue($subscription->onTrial());
        $this->assertTrue($subscription->isActive()); // TRIALING is considered active
    }

    #[Test]
    public function it_handles_past_due_subscription(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'sub_test_past_due',
            'amount' => 99.99,
            'currency' => 'SAR',
            'status' => 'PAST_DUE',
        ])));

        $subscription = $this->subscriptionService->retrieve('sub_test_past_due');

        $this->assertTrue($subscription->requiresAttention());
        $this->assertFalse($subscription->isActive());
    }

    #[Test]
    public function it_handles_expired_subscription(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'sub_test_expired',
            'amount' => 99.99,
            'currency' => 'SAR',
            'status' => 'EXPIRED',
        ])));

        $subscription = $this->subscriptionService->retrieve('sub_test_expired');

        $this->assertFalse($subscription->isActive());
        $this->assertFalse($subscription->isPaused());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->subscriptionService->create([
            'amount' => 99.99,
        ]);
    }

    #[Test]
    public function it_throws_api_error_exception_on_404(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Subscription not found',
        ])));

        try {
            $this->subscriptionService->retrieve('sub_nonexistent');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Subscription not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }
}
