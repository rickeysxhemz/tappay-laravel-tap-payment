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
        $this->assertFalse(ChargeStatus::DECLINED->isSuccessful());
        $this->assertFalse(ChargeStatus::CANCELLED->isSuccessful());
        $this->assertFalse(ChargeStatus::VOID->isSuccessful());
        $this->assertFalse(ChargeStatus::UNKNOWN->isSuccessful());
    }

    #[Test]
    public function it_can_check_if_charge_is_pending(): void
    {
        $this->assertTrue(ChargeStatus::INITIATED->isPending());
        $this->assertTrue(ChargeStatus::IN_PROGRESS->isPending());
        $this->assertFalse(ChargeStatus::CAPTURED->isPending());
        $this->assertFalse(ChargeStatus::FAILED->isPending());
        $this->assertFalse(ChargeStatus::AUTHORIZED->isPending());
        $this->assertFalse(ChargeStatus::DECLINED->isPending());
    }

    #[Test]
    public function it_can_check_if_charge_has_failed(): void
    {
        $this->assertTrue(ChargeStatus::FAILED->hasFailed());
        $this->assertTrue(ChargeStatus::DECLINED->hasFailed());
        $this->assertTrue(ChargeStatus::CANCELLED->hasFailed());
        $this->assertTrue(ChargeStatus::ABANDONED->hasFailed());
        $this->assertTrue(ChargeStatus::RESTRICTED->hasFailed());
        $this->assertTrue(ChargeStatus::TIMEDOUT->hasFailed());
        $this->assertFalse(ChargeStatus::CAPTURED->hasFailed());
        $this->assertFalse(ChargeStatus::AUTHORIZED->hasFailed());
        $this->assertFalse(ChargeStatus::INITIATED->hasFailed());
    }

    #[Test]
    public function it_returns_correct_string_values(): void
    {
        $this->assertSame('CAPTURED', ChargeStatus::CAPTURED->value);
        $this->assertSame('FAILED', ChargeStatus::FAILED->value);
        $this->assertSame('INITIATED', ChargeStatus::INITIATED->value);
        $this->assertSame('AUTHORIZED', ChargeStatus::AUTHORIZED->value);
        $this->assertSame('DECLINED', ChargeStatus::DECLINED->value);
        $this->assertSame('CANCELLED', ChargeStatus::CANCELLED->value);
        $this->assertSame('VOID', ChargeStatus::VOID->value);
        $this->assertSame('UNKNOWN', ChargeStatus::UNKNOWN->value);
    }

    #[Test]
    public function it_returns_correct_labels(): void
    {
        $this->assertSame('Captured', ChargeStatus::CAPTURED->label());
        $this->assertSame('Failed', ChargeStatus::FAILED->label());
        $this->assertSame('Initiated', ChargeStatus::INITIATED->label());
        $this->assertSame('Authorized', ChargeStatus::AUTHORIZED->label());
        $this->assertSame('Declined', ChargeStatus::DECLINED->label());
        $this->assertSame('Cancelled', ChargeStatus::CANCELLED->label());
        $this->assertSame('Abandoned', ChargeStatus::ABANDONED->label());
        $this->assertSame('Restricted', ChargeStatus::RESTRICTED->label());
        $this->assertSame('Void', ChargeStatus::VOID->label());
        $this->assertSame('Timed Out', ChargeStatus::TIMEDOUT->label());
        $this->assertSame('In Progress', ChargeStatus::IN_PROGRESS->label());
        $this->assertSame('Unknown', ChargeStatus::UNKNOWN->label());
    }

    #[Test]
    public function it_has_all_expected_statuses(): void
    {
        $expected = [
            'INITIATED', 'CAPTURED', 'AUTHORIZED', 'FAILED', 'DECLINED',
            'CANCELLED', 'ABANDONED', 'RESTRICTED', 'VOID', 'TIMEDOUT',
            'IN_PROGRESS', 'UNKNOWN',
        ];

        foreach ($expected as $status) {
            $enum = ChargeStatus::tryFrom($status);
            $this->assertNotNull($enum, "Status {$status} should exist");
            $this->assertInstanceOf(ChargeStatus::class, $enum);
        }
    }

    #[Test]
    public function it_returns_null_for_invalid_status(): void
    {
        $this->assertNull(ChargeStatus::tryFrom('INVALID_STATUS'));
        $this->assertNull(ChargeStatus::tryFrom(''));
        $this->assertNull(ChargeStatus::tryFrom('pending'));
    }

    #[Test]
    public function it_groups_statuses_correctly(): void
    {
        // Successful statuses
        $successful = [ChargeStatus::CAPTURED, ChargeStatus::AUTHORIZED];
        foreach ($successful as $status) {
            $this->assertTrue($status->isSuccessful(), "{$status->value} should be successful");
            $this->assertFalse($status->isPending(), "{$status->value} should not be pending");
            $this->assertFalse($status->hasFailed(), "{$status->value} should not be failed");
        }

        // Pending statuses
        $pending = [ChargeStatus::INITIATED, ChargeStatus::IN_PROGRESS];
        foreach ($pending as $status) {
            $this->assertTrue($status->isPending(), "{$status->value} should be pending");
            $this->assertFalse($status->isSuccessful(), "{$status->value} should not be successful");
            $this->assertFalse($status->hasFailed(), "{$status->value} should not be failed");
        }

        // Failed statuses
        $failed = [
            ChargeStatus::FAILED, ChargeStatus::DECLINED, ChargeStatus::CANCELLED,
            ChargeStatus::ABANDONED, ChargeStatus::RESTRICTED, ChargeStatus::TIMEDOUT,
        ];
        foreach ($failed as $status) {
            $this->assertTrue($status->hasFailed(), "{$status->value} should be failed");
            $this->assertFalse($status->isSuccessful(), "{$status->value} should not be successful");
            $this->assertFalse($status->isPending(), "{$status->value} should not be pending");
        }
    }
}
