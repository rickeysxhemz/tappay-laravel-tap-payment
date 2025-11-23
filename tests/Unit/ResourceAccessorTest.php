<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use TapPay\Tap\Enums\AuthorizeStatus;
use TapPay\Tap\Enums\ChargeStatus;
use TapPay\Tap\Enums\RefundStatus;
use TapPay\Tap\Resources\Authorize;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Resources\Customer;
use TapPay\Tap\Resources\Refund;
use TapPay\Tap\Resources\Token;

class ResourceAccessorTest extends TestCase
{
    // ==================== Charge Resource Tests ====================

    #[Test]
    public function charge_returns_all_attributes(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 50.75,
            'currency' => 'KWD',
            'status' => 'CAPTURED',
            'description' => 'Test payment',
            'transaction' => ['url' => 'https://tap.company/redirect/123'],
            'customer' => ['id' => 'cus_test_456'],
            'source' => ['id' => 'src_card'],
            'card' => ['id' => 'card_test_789'],
            'metadata' => ['order_id' => '12345'],
        ]);

        $this->assertSame('chg_test_123', $charge->id());
        $this->assertSame(50.75, $charge->amount());
        $this->assertSame('KWD', $charge->currency());
        $this->assertSame(ChargeStatus::CAPTURED, $charge->status());
        $this->assertSame('Test payment', $charge->description());
        $this->assertSame('https://tap.company/redirect/123', $charge->transactionUrl());
        $this->assertSame('cus_test_456', $charge->customerId());
        $this->assertSame('src_card', $charge->sourceId());
        $this->assertSame('card_test_789', $charge->cardId());
        $this->assertSame(['order_id' => '12345'], $charge->metadata());
    }

    #[Test]
    public function charge_handles_missing_optional_fields(): void
    {
        $charge = new Charge([
            'id' => 'chg_minimal',
            'amount' => 10.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
        ]);

        $this->assertNull($charge->description());
        $this->assertNull($charge->transactionUrl());
        $this->assertNull($charge->customerId());
        $this->assertNull($charge->sourceId());
        $this->assertNull($charge->cardId());
        $this->assertEmpty($charge->metadata());
    }

    #[Test]
    public function charge_handles_empty_id(): void
    {
        $charge = new Charge([
            'amount' => 10.00,
            'currency' => 'USD',
        ]);

        $this->assertSame('', $charge->id());
    }

    #[Test]
    public function charge_handles_zero_amount(): void
    {
        $charge = new Charge([]);

        $this->assertSame(0.0, $charge->amount());
    }

    #[Test]
    public function charge_status_helpers_work_correctly(): void
    {
        $capturedCharge = new Charge(['status' => 'CAPTURED']);
        $this->assertTrue($capturedCharge->isSuccessful());
        $this->assertFalse($capturedCharge->isPending());
        $this->assertFalse($capturedCharge->hasFailed());

        $initiatedCharge = new Charge(['status' => 'INITIATED']);
        $this->assertFalse($initiatedCharge->isSuccessful());
        $this->assertTrue($initiatedCharge->isPending());
        $this->assertFalse($initiatedCharge->hasFailed());

        $failedCharge = new Charge(['status' => 'FAILED']);
        $this->assertFalse($failedCharge->isSuccessful());
        $this->assertFalse($failedCharge->isPending());
        $this->assertTrue($failedCharge->hasFailed());
    }

    // ==================== Customer Resource Tests ====================

    #[Test]
    public function customer_returns_all_attributes(): void
    {
        $customer = new Customer([
            'id' => 'cus_test_123',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => [
                'country_code' => '965',
                'number' => '51234567',
            ],
            'metadata' => ['role' => 'premium'],
        ]);

        $this->assertSame('cus_test_123', $customer->id());
        $this->assertSame('John', $customer->firstName());
        $this->assertSame('Doe', $customer->lastName());
        $this->assertSame('john@example.com', $customer->email());
        $this->assertIsArray($customer->phone());
        $this->assertSame('965', $customer->phone()['country_code']);
        $this->assertSame('51234567', $customer->phone()['number']);
        $this->assertSame(['role' => 'premium'], $customer->metadata());
    }

    #[Test]
    public function customer_handles_missing_optional_fields(): void
    {
        $customer = new Customer([
            'id' => 'cus_minimal',
            'first_name' => 'Jane',
        ]);

        $this->assertNull($customer->lastName());
        $this->assertNull($customer->email());
        $this->assertNull($customer->phone());
        $this->assertEmpty($customer->metadata());
    }

    #[Test]
    public function customer_returns_empty_string_for_missing_first_name(): void
    {
        $customer = new Customer(['id' => 'cus_no_name']);

        $this->assertSame('', $customer->firstName());
    }

    // ==================== Token Resource Tests ====================

    #[Test]
    public function token_returns_all_attributes(): void
    {
        $token = new Token([
            'id' => 'tok_test_123',
            'card' => 'card_test_456',
            'customer' => 'cus_test_789',
            'created' => 1640000000,
        ]);

        $this->assertSame('tok_test_123', $token->id());
        $this->assertSame('card_test_456', $token->cardId());
        $this->assertSame('cus_test_789', $token->customerId());
        $this->assertSame(1640000000, $token->created());
    }

    #[Test]
    public function token_handles_missing_optional_fields(): void
    {
        $token = new Token([
            'id' => 'tok_minimal',
        ]);

        $this->assertNull($token->cardId());
        $this->assertNull($token->customerId());
        $this->assertNull($token->created());
    }

    #[Test]
    public function token_returns_empty_string_for_missing_id(): void
    {
        $token = new Token([]);

        $this->assertSame('', $token->id());
    }

    // ==================== Refund Resource Tests ====================

    #[Test]
    public function refund_returns_all_attributes(): void
    {
        $refund = new Refund([
            'id' => 'ref_test_123',
            'amount' => 25.50,
            'currency' => 'SAR',
            'status' => 'SUCCEEDED',
            'charge_id' => 'chg_test_456',
            'reason' => 'Customer request',
            'metadata' => ['refund_type' => 'partial'],
        ]);

        $this->assertSame('ref_test_123', $refund->id());
        $this->assertSame(25.50, $refund->amount());
        $this->assertSame('SAR', $refund->currency());
        $this->assertSame(RefundStatus::SUCCEEDED, $refund->status());
        $this->assertSame('chg_test_456', $refund->chargeId());
        $this->assertSame('Customer request', $refund->reason());
        $this->assertSame(['refund_type' => 'partial'], $refund->metadata());
    }

    #[Test]
    public function refund_handles_missing_optional_fields(): void
    {
        $refund = new Refund([
            'id' => 'ref_minimal',
            'amount' => 10.00,
            'currency' => 'USD',
            'status' => 'PENDING',
        ]);

        $this->assertSame('', $refund->chargeId());
        $this->assertNull($refund->reason());
        $this->assertEmpty($refund->metadata());
    }

    #[Test]
    public function refund_status_helpers_work_correctly(): void
    {
        $succeededRefund = new Refund(['status' => 'SUCCEEDED']);
        $this->assertTrue($succeededRefund->isSuccessful());
        $this->assertFalse($succeededRefund->isPending());
        $this->assertFalse($succeededRefund->hasFailed());

        $pendingRefund = new Refund(['status' => 'PENDING']);
        $this->assertFalse($pendingRefund->isSuccessful());
        $this->assertTrue($pendingRefund->isPending());
        $this->assertFalse($pendingRefund->hasFailed());

        $failedRefund = new Refund(['status' => 'FAILED']);
        $this->assertFalse($failedRefund->isSuccessful());
        $this->assertFalse($failedRefund->isPending());
        $this->assertTrue($failedRefund->hasFailed());
    }

    // ==================== Authorize Resource Tests ====================

    #[Test]
    public function authorize_returns_all_attributes(): void
    {
        $authorize = new Authorize([
            'id' => 'auth_test_123',
            'amount' => 100.00,
            'currency' => 'BHD',
            'status' => 'AUTHORIZED',
            'transaction' => ['url' => 'https://tap.company/redirect/auth/123'],
            'customer' => ['id' => 'cus_test_456'],
            'source' => ['id' => 'src_card'],
            'metadata' => ['authorization_type' => 'hold'],
        ]);

        $this->assertSame('auth_test_123', $authorize->id());
        $this->assertSame(100.00, $authorize->amount());
        $this->assertSame('BHD', $authorize->currency());
        $this->assertSame(AuthorizeStatus::AUTHORIZED, $authorize->status());
        $this->assertSame('https://tap.company/redirect/auth/123', $authorize->transactionUrl());
        $this->assertSame('cus_test_456', $authorize->customerId());
        $this->assertSame('src_card', $authorize->sourceId());
        $this->assertSame(['authorization_type' => 'hold'], $authorize->metadata());
    }

    #[Test]
    public function authorize_handles_missing_optional_fields(): void
    {
        $authorize = new Authorize([
            'id' => 'auth_minimal',
            'amount' => 50.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
        ]);

        $this->assertNull($authorize->transactionUrl());
        $this->assertNull($authorize->customerId());
        $this->assertNull($authorize->sourceId());
        $this->assertEmpty($authorize->metadata());
    }

    #[Test]
    public function authorize_status_helpers_work_correctly(): void
    {
        $authorizedAuth = new Authorize(['status' => 'AUTHORIZED']);
        $this->assertTrue($authorizedAuth->isAuthorized());
        $this->assertFalse($authorizedAuth->isPending());
        $this->assertFalse($authorizedAuth->hasFailed());

        $initiatedAuth = new Authorize(['status' => 'INITIATED']);
        $this->assertFalse($initiatedAuth->isAuthorized());
        $this->assertTrue($initiatedAuth->isPending());
        $this->assertFalse($initiatedAuth->hasFailed());

        $failedAuth = new Authorize(['status' => 'FAILED']);
        $this->assertFalse($failedAuth->isAuthorized());
        $this->assertFalse($failedAuth->isPending());
        $this->assertTrue($failedAuth->hasFailed());
    }

    // ==================== Edge Cases ====================

    #[Test]
    public function resources_handle_completely_empty_data(): void
    {
        $charge = new Charge([]);
        $this->assertSame('', $charge->id());
        $this->assertSame(0.0, $charge->amount());
        $this->assertSame('', $charge->currency());

        $customer = new Customer([]);
        $this->assertSame('', $customer->id());
        $this->assertSame('', $customer->firstName());

        $token = new Token([]);
        $this->assertSame('', $token->id());

        $refund = new Refund([]);
        $this->assertSame('', $refund->id());
        $this->assertSame(0.0, $refund->amount());

        $authorize = new Authorize([]);
        $this->assertSame('', $authorize->id());
        $this->assertSame(0.0, $authorize->amount());
    }

    #[Test]
    public function resources_handle_nested_null_values(): void
    {
        $charge = new Charge([
            'transaction' => null,
            'customer' => null,
            'source' => null,
            'card' => null,
        ]);

        $this->assertNull($charge->transactionUrl());
        $this->assertNull($charge->customerId());
        $this->assertNull($charge->sourceId());
        $this->assertNull($charge->cardId());
    }

    #[Test]
    public function customer_phone_handles_various_formats(): void
    {
        $customerWithPhone = new Customer([
            'phone' => [
                'country_code' => '1',
                'number' => '5551234567',
            ],
        ]);

        $phone = $customerWithPhone->phone();
        $this->assertIsArray($phone);
        $this->assertSame('1', $phone['country_code']);
        $this->assertSame('5551234567', $phone['number']);

        $customerWithoutPhone = new Customer([]);
        $this->assertNull($customerWithoutPhone->phone());
    }

    #[Test]
    public function all_resources_handle_string_amounts_correctly(): void
    {
        $charge = new Charge(['amount' => '123.45']);
        $this->assertSame(123.45, $charge->amount());

        $refund = new Refund(['amount' => '67.89']);
        $this->assertSame(67.89, $refund->amount());

        $authorize = new Authorize(['amount' => '999.99']);
        $this->assertSame(999.99, $authorize->amount());
    }

    #[Test]
    public function resources_handle_unknown_status_gracefully(): void
    {
        $charge = new Charge(['status' => 'INVALID_STATUS']);
        $this->assertSame(ChargeStatus::UNKNOWN, $charge->status());

        $refund = new Refund(['status' => 'INVALID_STATUS']);
        $this->assertSame(RefundStatus::FAILED, $refund->status());

        $authorize = new Authorize(['status' => 'INVALID_STATUS']);
        $this->assertSame(AuthorizeStatus::UNKNOWN, $authorize->status());
    }

    #[Test]
    public function metadata_returns_immutable_copy(): void
    {
        $charge = new Charge([
            'metadata' => ['key1' => 'value1', 'key2' => 'value2'],
        ]);

        $metadata = $charge->metadata();
        $this->assertSame(['key1' => 'value1', 'key2' => 'value2'], $metadata);

        // Verify it's an array
        $this->assertIsArray($metadata);
    }
}