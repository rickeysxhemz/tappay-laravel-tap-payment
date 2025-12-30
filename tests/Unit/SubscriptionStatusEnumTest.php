<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Enums\SubscriptionStatus;
use TapPay\Tap\Tests\TestCase;

class SubscriptionStatusEnumTest extends TestCase
{
    #[Test]
    public function it_returns_correct_label_for_active(): void
    {
        $this->assertSame('Active', SubscriptionStatus::ACTIVE->label());
    }

    #[Test]
    public function it_returns_correct_label_for_paused(): void
    {
        $this->assertSame('Paused', SubscriptionStatus::PAUSED->label());
    }

    #[Test]
    public function it_returns_correct_label_for_cancelled(): void
    {
        $this->assertSame('Cancelled', SubscriptionStatus::CANCELLED->label());
    }

    #[Test]
    public function it_returns_correct_label_for_expired(): void
    {
        $this->assertSame('Expired', SubscriptionStatus::EXPIRED->label());
    }

    #[Test]
    public function it_returns_correct_label_for_past_due(): void
    {
        $this->assertSame('Past Due', SubscriptionStatus::PAST_DUE->label());
    }

    #[Test]
    public function it_returns_correct_label_for_trialing(): void
    {
        $this->assertSame('Trialing', SubscriptionStatus::TRIALING->label());
    }

    #[Test]
    public function is_active_returns_true_for_active_and_trialing(): void
    {
        $this->assertTrue(SubscriptionStatus::ACTIVE->isActive());
        $this->assertTrue(SubscriptionStatus::TRIALING->isActive());
        $this->assertFalse(SubscriptionStatus::PAUSED->isActive());
        $this->assertFalse(SubscriptionStatus::CANCELLED->isActive());
        $this->assertFalse(SubscriptionStatus::EXPIRED->isActive());
        $this->assertFalse(SubscriptionStatus::PAST_DUE->isActive());
    }

    #[Test]
    public function is_paused_returns_true_only_for_paused(): void
    {
        $this->assertTrue(SubscriptionStatus::PAUSED->isPaused());
        $this->assertFalse(SubscriptionStatus::ACTIVE->isPaused());
        $this->assertFalse(SubscriptionStatus::TRIALING->isPaused());
        $this->assertFalse(SubscriptionStatus::CANCELLED->isPaused());
        $this->assertFalse(SubscriptionStatus::EXPIRED->isPaused());
        $this->assertFalse(SubscriptionStatus::PAST_DUE->isPaused());
    }

    #[Test]
    public function is_cancelled_returns_true_for_cancelled_and_expired(): void
    {
        $this->assertTrue(SubscriptionStatus::CANCELLED->isCancelled());
        $this->assertTrue(SubscriptionStatus::EXPIRED->isCancelled());
        $this->assertFalse(SubscriptionStatus::ACTIVE->isCancelled());
        $this->assertFalse(SubscriptionStatus::TRIALING->isCancelled());
        $this->assertFalse(SubscriptionStatus::PAUSED->isCancelled());
        $this->assertFalse(SubscriptionStatus::PAST_DUE->isCancelled());
    }

    #[Test]
    public function requires_attention_returns_true_only_for_past_due(): void
    {
        $this->assertTrue(SubscriptionStatus::PAST_DUE->requiresAttention());
        $this->assertFalse(SubscriptionStatus::ACTIVE->requiresAttention());
        $this->assertFalse(SubscriptionStatus::TRIALING->requiresAttention());
        $this->assertFalse(SubscriptionStatus::PAUSED->requiresAttention());
        $this->assertFalse(SubscriptionStatus::CANCELLED->requiresAttention());
        $this->assertFalse(SubscriptionStatus::EXPIRED->requiresAttention());
    }

    #[Test]
    public function it_can_be_created_from_string_value(): void
    {
        $this->assertSame(SubscriptionStatus::ACTIVE, SubscriptionStatus::from('ACTIVE'));
        $this->assertSame(SubscriptionStatus::PAUSED, SubscriptionStatus::from('PAUSED'));
        $this->assertSame(SubscriptionStatus::CANCELLED, SubscriptionStatus::from('CANCELLED'));
        $this->assertSame(SubscriptionStatus::EXPIRED, SubscriptionStatus::from('EXPIRED'));
        $this->assertSame(SubscriptionStatus::PAST_DUE, SubscriptionStatus::from('PAST_DUE'));
        $this->assertSame(SubscriptionStatus::TRIALING, SubscriptionStatus::from('TRIALING'));
    }

    #[Test]
    public function it_returns_all_cases(): void
    {
        $cases = SubscriptionStatus::cases();

        $this->assertCount(6, $cases);
        $this->assertContains(SubscriptionStatus::ACTIVE, $cases);
        $this->assertContains(SubscriptionStatus::PAUSED, $cases);
        $this->assertContains(SubscriptionStatus::CANCELLED, $cases);
        $this->assertContains(SubscriptionStatus::EXPIRED, $cases);
        $this->assertContains(SubscriptionStatus::PAST_DUE, $cases);
        $this->assertContains(SubscriptionStatus::TRIALING, $cases);
    }
}
