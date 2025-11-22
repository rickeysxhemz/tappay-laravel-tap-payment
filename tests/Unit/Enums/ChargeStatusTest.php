<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit\Enums;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TapPay\Tap\Enums\ChargeStatus;

class ChargeStatusTest extends TestCase
{
    #[Test]
    public function it_can_check_if_charge_is_successful(): void
    {
        $this->assertTrue(ChargeStatus::CAPTURED->isSuccessful());
        $this->assertTrue(ChargeStatus::AUTHORIZED->isSuccessful());
        $this->assertFalse(ChargeStatus::FAILED->isSuccessful());
        $this->assertFalse(ChargeStatus::INITIATED->isSuccessful());
    }

    #[Test]
    public function it_can_check_if_charge_is_pending(): void
    {
        $this->assertTrue(ChargeStatus::INITIATED->isPending());
        $this->assertTrue(ChargeStatus::IN_PROGRESS->isPending());
        $this->assertFalse(ChargeStatus::CAPTURED->isPending());
        $this->assertFalse(ChargeStatus::FAILED->isPending());
    }

    #[Test]
    public function it_can_check_if_charge_has_failed(): void
    {
        $this->assertTrue(ChargeStatus::FAILED->hasFailed());
        $this->assertTrue(ChargeStatus::DECLINED->hasFailed());
        $this->assertTrue(ChargeStatus::CANCELLED->hasFailed());
        $this->assertTrue(ChargeStatus::ABANDONED->hasFailed());
        $this->assertFalse(ChargeStatus::CAPTURED->hasFailed());
    }

    #[Test]
    public function it_returns_correct_string_values(): void
    {
        $this->assertSame('CAPTURED', ChargeStatus::CAPTURED->value);
        $this->assertSame('FAILED', ChargeStatus::FAILED->value);
        $this->assertSame('INITIATED', ChargeStatus::INITIATED->value);
    }
}