<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Resources\Payout;
use TapPay\Tap\Tests\TestCase;

class PayoutResourceTest extends TestCase
{
    #[Test]
    public function can_create_payout_resource_from_array(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123456',
            'merchant' => 'merchant_test_789',
            'amount' => 1000.00,
            'currency' => 'SAR',
            'status' => 'PAID',
            'arrival_date' => '2024-01-15',
            'period_start' => '2024-01-01',
            'period_end' => '2024-01-07',
            'transaction_count' => 25,
            'fee' => 10.00,
        ]);

        $this->assertSame('payout_test_123456', $payout->id());
        $this->assertSame('merchant_test_789', $payout->merchantId());
        $this->assertSame(1000.00, $payout->amount()->toDecimal());
        $this->assertSame('SAR', $payout->currency());
        $this->assertSame('PAID', $payout->status());
        $this->assertSame('2024-01-15', $payout->arrivalDate());
        $this->assertSame('2024-01-01', $payout->periodStart());
        $this->assertSame('2024-01-07', $payout->periodEnd());
        $this->assertSame(25, $payout->transactionCount());
    }

    #[Test]
    public function is_pending_returns_true_for_pending_status(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
            'status' => 'PENDING',
        ]);

        $this->assertTrue($payout->isPending());
        $this->assertFalse($payout->isComplete());
        $this->assertFalse($payout->isFailed());
    }

    #[Test]
    public function is_pending_returns_true_for_in_progress_status(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
            'status' => 'IN_PROGRESS',
        ]);

        $this->assertTrue($payout->isPending());
    }

    #[Test]
    public function is_complete_returns_true_for_paid_status(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
            'status' => 'PAID',
        ]);

        $this->assertTrue($payout->isComplete());
        $this->assertFalse($payout->isPending());
        $this->assertFalse($payout->isFailed());
    }

    #[Test]
    public function is_failed_returns_true_for_failed_status(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
            'status' => 'FAILED',
        ]);

        $this->assertTrue($payout->isFailed());
        $this->assertFalse($payout->isPending());
        $this->assertFalse($payout->isComplete());
    }

    #[Test]
    public function can_get_merchant_id_from_merchant_field(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'merchant' => 'merchant_test_789',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertSame('merchant_test_789', $payout->merchantId());
    }

    #[Test]
    public function can_get_merchant_id_from_merchant_id_field(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'merchant_id' => 'merchant_test_789',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertSame('merchant_test_789', $payout->merchantId());
    }

    #[Test]
    public function merchant_id_returns_null_when_not_set(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($payout->merchantId());
    }

    #[Test]
    public function can_get_bank_account(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
            'bank_account' => [
                'iban' => 'SA0380000000608010167519',
                'bank_name' => 'Al Rajhi Bank',
            ],
        ]);

        $this->assertIsArray($payout->bankAccount());
        $this->assertSame('Al Rajhi Bank', $payout->bankAccount()['bank_name']);
    }

    #[Test]
    public function bank_account_returns_null_when_not_set(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($payout->bankAccount());
    }

    #[Test]
    public function can_calculate_fee_amount(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'fee' => 2.50,
        ]);

        $this->assertSame(2.50, $payout->feeAmount()->toDecimal());
    }

    #[Test]
    public function can_calculate_net_amount(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'fee' => 2.50,
        ]);

        $this->assertSame(97.50, $payout->netAmount()->toDecimal());
    }

    #[Test]
    public function status_returns_null_when_not_set(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($payout->status());
    }

    #[Test]
    public function arrival_date_returns_null_when_not_set(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($payout->arrivalDate());
    }

    #[Test]
    public function period_start_returns_null_when_not_set(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($payout->periodStart());
    }

    #[Test]
    public function period_end_returns_null_when_not_set(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($payout->periodEnd());
    }

    #[Test]
    public function transaction_count_returns_zero_when_not_set(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertSame(0, $payout->transactionCount());
    }

    #[Test]
    public function has_valid_id_returns_true_for_valid_payout_id(): void
    {
        $payout = new Payout([
            'id' => 'payout_test_123',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertTrue($payout->hasValidId());
    }

    #[Test]
    public function has_valid_id_returns_false_for_invalid_payout_id(): void
    {
        $payout = new Payout([
            'id' => 'invalid_id',
            'amount' => 500.00,
            'currency' => 'SAR',
        ]);

        $this->assertFalse($payout->hasValidId());
    }
}
