<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Resources\Destination;
use TapPay\Tap\Tests\TestCase;

class DestinationResourceTest extends TestCase
{
    #[Test]
    public function can_create_destination_resource_from_array(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123456',
            'merchant' => 'merchant_test_789',
            'amount' => 50.00,
            'currency' => 'SAR',
            'status' => 'PENDING',
            'charge' => 'chg_test_abc',
        ]);

        $this->assertSame('dest_test_123456', $destination->id());
        $this->assertSame('merchant_test_789', $destination->merchantId());
        $this->assertSame(50.00, $destination->amount()->toDecimal());
        $this->assertSame('SAR', $destination->currency());
        $this->assertSame('PENDING', $destination->status());
        $this->assertSame('chg_test_abc', $destination->chargeId());
    }

    #[Test]
    public function is_pending_returns_true_for_pending_status(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'amount' => 50.00,
            'currency' => 'SAR',
            'status' => 'PENDING',
        ]);

        $this->assertTrue($destination->isPending());
        $this->assertFalse($destination->isComplete());
    }

    #[Test]
    public function is_complete_returns_true_for_transferred_status(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'amount' => 50.00,
            'currency' => 'SAR',
            'status' => 'TRANSFERRED',
        ]);

        $this->assertTrue($destination->isComplete());
        $this->assertFalse($destination->isPending());
    }

    #[Test]
    public function can_get_merchant_id_from_merchant_field(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'merchant' => 'merchant_test_789',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertSame('merchant_test_789', $destination->merchantId());
    }

    #[Test]
    public function can_get_merchant_id_from_merchant_id_field(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'merchant_id' => 'merchant_test_789',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertSame('merchant_test_789', $destination->merchantId());
    }

    #[Test]
    public function merchant_id_returns_null_when_not_set(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($destination->merchantId());
    }

    #[Test]
    public function can_get_transfer_id(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'amount' => 50.00,
            'currency' => 'SAR',
            'transfer' => 'tr_test_456',
        ]);

        $this->assertSame('tr_test_456', $destination->transferId());
    }

    #[Test]
    public function transfer_id_returns_null_when_not_set(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($destination->transferId());
    }

    #[Test]
    public function can_get_charge_id_from_charge_field(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'charge' => 'chg_test_abc',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertSame('chg_test_abc', $destination->chargeId());
    }

    #[Test]
    public function can_get_charge_id_from_charge_id_field(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'charge_id' => 'chg_test_abc',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertSame('chg_test_abc', $destination->chargeId());
    }

    #[Test]
    public function charge_id_returns_null_when_not_set(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($destination->chargeId());
    }

    #[Test]
    public function status_returns_null_when_not_set(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertNull($destination->status());
    }

    #[Test]
    public function has_valid_id_returns_true_for_valid_destination_id(): void
    {
        $destination = new Destination([
            'id' => 'dest_test_123',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertTrue($destination->hasValidId());
    }

    #[Test]
    public function has_valid_id_returns_false_for_invalid_destination_id(): void
    {
        $destination = new Destination([
            'id' => 'invalid_id',
            'amount' => 50.00,
            'currency' => 'SAR',
        ]);

        $this->assertFalse($destination->hasValidId());
    }
}
