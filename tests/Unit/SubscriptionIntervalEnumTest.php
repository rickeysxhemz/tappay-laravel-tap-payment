<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Enums\SubscriptionInterval;
use TapPay\Tap\Tests\TestCase;

class SubscriptionIntervalEnumTest extends TestCase
{
    #[Test]
    public function it_has_daily_case(): void
    {
        $this->assertSame('DAILY', SubscriptionInterval::DAILY->value);
    }

    #[Test]
    public function it_has_weekly_case(): void
    {
        $this->assertSame('WEEKLY', SubscriptionInterval::WEEKLY->value);
    }

    #[Test]
    public function it_has_monthly_case(): void
    {
        $this->assertSame('MONTHLY', SubscriptionInterval::MONTHLY->value);
    }

    #[Test]
    public function it_has_yearly_case(): void
    {
        $this->assertSame('YEARLY', SubscriptionInterval::YEARLY->value);
    }

    #[Test]
    public function it_returns_correct_label_for_daily(): void
    {
        $this->assertSame('Daily', SubscriptionInterval::DAILY->label());
    }

    #[Test]
    public function it_returns_correct_label_for_weekly(): void
    {
        $this->assertSame('Weekly', SubscriptionInterval::WEEKLY->label());
    }

    #[Test]
    public function it_returns_correct_label_for_monthly(): void
    {
        $this->assertSame('Monthly', SubscriptionInterval::MONTHLY->label());
    }

    #[Test]
    public function it_returns_correct_label_for_yearly(): void
    {
        $this->assertSame('Yearly', SubscriptionInterval::YEARLY->label());
    }

    #[Test]
    public function it_returns_correct_days_for_daily(): void
    {
        $this->assertSame(1, SubscriptionInterval::DAILY->days());
    }

    #[Test]
    public function it_returns_correct_days_for_weekly(): void
    {
        $this->assertSame(7, SubscriptionInterval::WEEKLY->days());
    }

    #[Test]
    public function it_returns_correct_days_for_monthly(): void
    {
        $this->assertSame(30, SubscriptionInterval::MONTHLY->days());
    }

    #[Test]
    public function it_returns_correct_days_for_yearly(): void
    {
        $this->assertSame(365, SubscriptionInterval::YEARLY->days());
    }

    #[Test]
    public function it_can_be_created_from_string_value(): void
    {
        $this->assertSame(SubscriptionInterval::DAILY, SubscriptionInterval::from('DAILY'));
        $this->assertSame(SubscriptionInterval::WEEKLY, SubscriptionInterval::from('WEEKLY'));
        $this->assertSame(SubscriptionInterval::MONTHLY, SubscriptionInterval::from('MONTHLY'));
        $this->assertSame(SubscriptionInterval::YEARLY, SubscriptionInterval::from('YEARLY'));
    }

    #[Test]
    public function it_returns_all_cases(): void
    {
        $cases = SubscriptionInterval::cases();

        $this->assertCount(4, $cases);
        $this->assertContains(SubscriptionInterval::DAILY, $cases);
        $this->assertContains(SubscriptionInterval::WEEKLY, $cases);
        $this->assertContains(SubscriptionInterval::MONTHLY, $cases);
        $this->assertContains(SubscriptionInterval::YEARLY, $cases);
    }
}
