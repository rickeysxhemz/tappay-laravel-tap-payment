<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Enums\InvoiceStatus;
use TapPay\Tap\Tests\TestCase;

class InvoiceStatusEnumTest extends TestCase
{
    #[Test]
    public function it_returns_correct_label_for_draft(): void
    {
        $this->assertSame('Draft', InvoiceStatus::DRAFT->label());
    }

    #[Test]
    public function it_returns_correct_label_for_pending(): void
    {
        $this->assertSame('Pending', InvoiceStatus::PENDING->label());
    }

    #[Test]
    public function it_returns_correct_label_for_paid(): void
    {
        $this->assertSame('Paid', InvoiceStatus::PAID->label());
    }

    #[Test]
    public function it_returns_correct_label_for_cancelled(): void
    {
        $this->assertSame('Cancelled', InvoiceStatus::CANCELLED->label());
    }

    #[Test]
    public function it_returns_correct_label_for_expired(): void
    {
        $this->assertSame('Expired', InvoiceStatus::EXPIRED->label());
    }

    #[Test]
    public function it_returns_correct_label_for_failed(): void
    {
        $this->assertSame('Failed', InvoiceStatus::FAILED->label());
    }

    #[Test]
    public function is_successful_returns_true_only_for_paid(): void
    {
        $this->assertTrue(InvoiceStatus::PAID->isSuccessful());
        $this->assertFalse(InvoiceStatus::DRAFT->isSuccessful());
        $this->assertFalse(InvoiceStatus::PENDING->isSuccessful());
        $this->assertFalse(InvoiceStatus::CANCELLED->isSuccessful());
        $this->assertFalse(InvoiceStatus::EXPIRED->isSuccessful());
        $this->assertFalse(InvoiceStatus::FAILED->isSuccessful());
    }

    #[Test]
    public function is_pending_returns_true_for_draft_and_pending(): void
    {
        $this->assertTrue(InvoiceStatus::DRAFT->isPending());
        $this->assertTrue(InvoiceStatus::PENDING->isPending());
        $this->assertFalse(InvoiceStatus::PAID->isPending());
        $this->assertFalse(InvoiceStatus::CANCELLED->isPending());
        $this->assertFalse(InvoiceStatus::EXPIRED->isPending());
        $this->assertFalse(InvoiceStatus::FAILED->isPending());
    }

    #[Test]
    public function has_failed_returns_true_for_cancelled_expired_failed(): void
    {
        $this->assertTrue(InvoiceStatus::CANCELLED->hasFailed());
        $this->assertTrue(InvoiceStatus::EXPIRED->hasFailed());
        $this->assertTrue(InvoiceStatus::FAILED->hasFailed());
        $this->assertFalse(InvoiceStatus::DRAFT->hasFailed());
        $this->assertFalse(InvoiceStatus::PENDING->hasFailed());
        $this->assertFalse(InvoiceStatus::PAID->hasFailed());
    }

    #[Test]
    public function it_can_be_created_from_string_value(): void
    {
        $this->assertSame(InvoiceStatus::DRAFT, InvoiceStatus::from('DRAFT'));
        $this->assertSame(InvoiceStatus::PENDING, InvoiceStatus::from('PENDING'));
        $this->assertSame(InvoiceStatus::PAID, InvoiceStatus::from('PAID'));
        $this->assertSame(InvoiceStatus::CANCELLED, InvoiceStatus::from('CANCELLED'));
        $this->assertSame(InvoiceStatus::EXPIRED, InvoiceStatus::from('EXPIRED'));
        $this->assertSame(InvoiceStatus::FAILED, InvoiceStatus::from('FAILED'));
    }

    #[Test]
    public function it_returns_all_cases(): void
    {
        $cases = InvoiceStatus::cases();

        $this->assertCount(6, $cases);
        $this->assertContains(InvoiceStatus::DRAFT, $cases);
        $this->assertContains(InvoiceStatus::PENDING, $cases);
        $this->assertContains(InvoiceStatus::PAID, $cases);
        $this->assertContains(InvoiceStatus::CANCELLED, $cases);
        $this->assertContains(InvoiceStatus::EXPIRED, $cases);
        $this->assertContains(InvoiceStatus::FAILED, $cases);
    }
}
