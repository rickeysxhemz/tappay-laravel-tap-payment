<?php

declare(strict_types=1);

namespace Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Resources\Merchant;
use TapPay\Tap\Tests\TestCase;

class MerchantResourceTest extends TestCase
{
    #[Test]
    public function can_create_merchant_resource_from_array(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123456',
            'name' => 'Vendor Store',
            'email' => 'vendor@example.com',
            'country_code' => 'SA',
            'type' => 'company',
            'status' => 'ACTIVE',
        ]);

        $this->assertSame('merchant_test_123456', $merchant->id());
        $this->assertSame('Vendor Store', $merchant->name());
        $this->assertSame('vendor@example.com', $merchant->email());
        $this->assertSame('SA', $merchant->countryCode());
        $this->assertSame('company', $merchant->type());
    }

    #[Test]
    public function is_active_returns_true_for_active_status(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
            'status' => 'ACTIVE',
        ]);

        $this->assertTrue($merchant->isActive());
    }

    #[Test]
    public function is_active_returns_false_for_non_active_status(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
            'status' => 'PENDING',
        ]);

        $this->assertFalse($merchant->isActive());
    }

    #[Test]
    public function is_verified_returns_true_when_verification_status_is_verified(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
            'verification' => [
                'status' => 'VERIFIED',
            ],
        ]);

        $this->assertTrue($merchant->isVerified());
    }

    #[Test]
    public function is_verified_returns_false_when_verification_status_is_pending(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
            'verification' => [
                'status' => 'PENDING',
            ],
        ]);

        $this->assertFalse($merchant->isVerified());
    }

    #[Test]
    public function is_verified_returns_false_when_no_verification_data(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
        ]);

        $this->assertFalse($merchant->isVerified());
    }

    #[Test]
    public function can_get_business_details(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
            'business' => [
                'name' => 'Business LLC',
                'registration_number' => '123456789',
            ],
        ]);

        $this->assertIsArray($merchant->business());
        $this->assertSame('Business LLC', $merchant->business()['name']);
        $this->assertSame('123456789', $merchant->business()['registration_number']);
    }

    #[Test]
    public function business_returns_null_when_not_set(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
        ]);

        $this->assertNull($merchant->business());
    }

    #[Test]
    public function can_get_bank_account(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
            'bank_account' => [
                'iban' => 'SA0380000000608010167519',
                'bank_name' => 'Al Rajhi Bank',
            ],
        ]);

        $this->assertIsArray($merchant->bankAccount());
        $this->assertSame('Al Rajhi Bank', $merchant->bankAccount()['bank_name']);
    }

    #[Test]
    public function bank_account_returns_null_when_not_set(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
        ]);

        $this->assertNull($merchant->bankAccount());
    }

    #[Test]
    public function can_get_payout_schedule(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
            'payout_schedule' => 'WEEKLY',
        ]);

        $this->assertSame('WEEKLY', $merchant->payoutSchedule());
    }

    #[Test]
    public function payout_schedule_returns_null_when_not_set(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
        ]);

        $this->assertNull($merchant->payoutSchedule());
    }

    #[Test]
    public function has_valid_id_returns_true_for_valid_merchant_id(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
        ]);

        $this->assertTrue($merchant->hasValidId());
    }

    #[Test]
    public function has_valid_id_returns_false_for_invalid_merchant_id(): void
    {
        $merchant = new Merchant([
            'id' => 'invalid_id',
        ]);

        $this->assertFalse($merchant->hasValidId());
    }

    #[Test]
    public function returns_null_for_missing_name(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
        ]);

        $this->assertNull($merchant->name());
    }

    #[Test]
    public function returns_null_for_missing_country_code(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
        ]);

        $this->assertNull($merchant->countryCode());
    }

    #[Test]
    public function returns_null_for_missing_type(): void
    {
        $merchant = new Merchant([
            'id' => 'merchant_test_123',
        ]);

        $this->assertNull($merchant->type());
    }
}
