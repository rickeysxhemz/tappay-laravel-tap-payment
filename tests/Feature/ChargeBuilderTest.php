<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Enums\SourceObject;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Tests\TestCase;

class ChargeBuilderTest extends TestCase
{
    protected ChargeService $chargeService;

    protected MoneyContract $money;

    protected function setUp(): void
    {
        parent::setUp();

        $this->money = app(MoneyContract::class);
        $this->chargeService = new ChargeService($this->mockHttpClient(), $this->money);
    }

    protected function createBuilder(): ChargeBuilder
    {
        return new ChargeBuilder($this->chargeService, $this->money);
    }

    #[Test]
    public function it_builds_charge_with_fluent_interface(): void
    {
        $builder = $this->createBuilder();

        $data = $builder
            ->amount(10050)
            ->currency('KWD')
            ->description('Test payment')
            ->customerId('cus_test_123')
            ->withCard()
            ->saveCard()
            ->redirectUrl('https://example.com/success')
            ->postUrl('https://example.com/webhook')
            ->metadata(['order_id' => '12345'])
            ->toArray();

        // KWD has 3 decimal places: 10050 / 1000 = 10.05
        $this->assertSame(10.05, $data['amount']);
        $this->assertSame('KWD', $data['currency']);
        $this->assertSame('Test payment', $data['description']);
        $this->assertSame('cus_test_123', $data['customer']['id']);
        $this->assertSame('src_card', $data['source']['id']);
        $this->assertTrue($data['save_card']);
        $this->assertSame('https://example.com/success', $data['redirect']['url']);
        $this->assertSame('https://example.com/webhook', $data['post']['url']);
        $this->assertSame(['order_id' => '12345'], $data['metadata']);
    }

    #[Test]
    public function it_can_use_different_payment_methods(): void
    {
        $builder = $this->createBuilder();

        // Test KNET
        $data = $builder->amount(1000)->withKNET()->toArray();
        $this->assertSame('src_kw.knet', $data['source']['id']);

        // Test MADA
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withMADA()->toArray();
        $this->assertSame('src_sa.mada', $data['source']['id']);

        // Test Token
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withToken('tok_abc123')->toArray();
        $this->assertSame('tok_abc123', $data['source']['id']);
    }

    #[Test]
    public function it_can_set_metadata(): void
    {
        $builder = $this->createBuilder();

        $data = $builder
            ->amount(1000)
            ->metadata([
                'order_id' => '123',
                'user_id' => '456',
                'invoice' => 'INV-001',
            ])
            ->toArray();

        $this->assertSame([
            'order_id' => '123',
            'user_id' => '456',
            'invoice' => 'INV-001',
        ], $data['metadata']);
    }

    #[Test]
    public function it_can_set_receipt_options(): void
    {
        $builder = $this->createBuilder();

        $data = $builder
            ->amount(1000)
            ->emailReceipt(true)
            ->smsReceipt(true)
            ->toArray();

        $this->assertTrue($data['receipt']['email']);
        $this->assertTrue($data['receipt']['sms']);
    }

    #[Test]
    public function it_can_capture_authorization(): void
    {
        $builder = $this->createBuilder();

        $data = $builder
            ->amount(5000)
            ->captureAuthorization('auth_xyz789')
            ->toArray();

        $this->assertSame('auth_xyz789', $data['source']['id']);
    }

    #[Test]
    public function it_creates_charge_via_builder(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'chg_test_builder',
            'amount' => 25.00,
            'currency' => 'USD',
            'status' => 'INITIATED',
        ])));

        $charge = ($this->createBuilder())
            ->amount(2500)
            ->withCard()
            ->description('Builder test')
            ->create();

        $this->assertSame('chg_test_builder', $charge->id());
        $this->assertSame(25.00, $charge->amount());
    }

    #[Test]
    public function it_uses_default_currency_from_config(): void
    {
        config(['tap.currency' => 'KWD']);

        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->toArray();

        $this->assertSame('KWD', $data['currency']);
    }

    #[Test]
    public function it_can_override_default_currency(): void
    {
        config(['tap.currency' => 'KWD']);

        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->currency('SAR')
            ->toArray();

        $this->assertSame('SAR', $data['currency']);
    }

    #[Test]
    public function it_accepts_source_enum_or_string(): void
    {
        $builder = $this->createBuilder();

        // Test with enum
        $data = $builder->amount(1000)->source(SourceObject::SRC_BENEFIT)->toArray();
        $this->assertSame('src_bh.benefit', $data['source']['id']);

        // Test with string
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->source('src_custom')->toArray();
        $this->assertSame('src_custom', $data['source']['id']);
    }

    #[Test]
    public function it_can_check_if_field_exists(): void
    {
        $builder = $this->createBuilder();
        $builder->amount(10000)->currency('KWD');

        $this->assertTrue($builder->has('amount'));
        $this->assertTrue($builder->has('currency'));
        $this->assertFalse($builder->has('description'));
        $this->assertFalse($builder->has('nonexistent'));
    }

    #[Test]
    public function it_can_get_field_value(): void
    {
        $builder = $this->createBuilder();
        // KWD has 3 decimal places: 10000 / 1000 = 10.0
        $builder->amount(10000)->currency('KWD')->description('Test');

        $this->assertSame(10.0, $builder->get('amount'));
        $this->assertSame('KWD', $builder->get('currency'));
        $this->assertSame('Test', $builder->get('description'));
        $this->assertNull($builder->get('nonexistent'));
    }

    #[Test]
    public function it_can_get_field_with_default_value(): void
    {
        $builder = $this->createBuilder();
        $builder->amount(10000);

        $this->assertSame(100.0, $builder->get('amount'));
        $this->assertSame('SAR', $builder->get('currency')); // Builder has SAR from config
        $this->assertSame('default', $builder->get('nonexistent', 'default'));
    }

    #[Test]
    public function it_can_reset_builder(): void
    {
        $builder = $this->createBuilder();
        $builder->amount(10000)->currency('KWD')->description('Test');

        $this->assertTrue($builder->has('amount'));
        $this->assertTrue($builder->has('currency'));

        $builder->reset();

        $this->assertFalse($builder->has('amount'));
        $this->assertFalse($builder->has('currency'));
        $this->assertFalse($builder->has('description'));
        $this->assertEmpty($builder->toArray());
    }

    #[Test]
    public function it_can_set_reference(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(10000)
            ->reference('TX-12345')
            ->toArray();

        $this->assertSame('TX-12345', $data['reference']['transaction']);
    }

    #[Test]
    public function it_can_use_all_payment_methods(): void
    {
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withAllMethods()->toArray();

        $this->assertSame('src_all', $data['source']['id']);
    }

    #[Test]
    public function it_can_use_benefit_payment(): void
    {
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withBenefit()->toArray();

        $this->assertSame('src_bh.benefit', $data['source']['id']);
    }

    #[Test]
    public function it_can_use_omannet_payment(): void
    {
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withOmanNet()->toArray();

        $this->assertSame('src_om.omannet', $data['source']['id']);
    }

    #[Test]
    public function it_can_use_naps_payment(): void
    {
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->withNAPS()->toArray();

        $this->assertSame('src_qa.naps', $data['source']['id']);
    }

    #[Test]
    public function it_throws_exception_for_invalid_token_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Token ID must start with "tok_"');

        $builder = $this->createBuilder();
        $builder->withToken('invalid_token');
    }

    #[Test]
    public function it_throws_exception_for_invalid_authorization_id(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Authorization ID must start with "auth_"');

        $builder = $this->createBuilder();
        $builder->captureAuthorization('invalid_auth');
    }

    #[Test]
    public function it_can_disable_save_card(): void
    {
        $builder = $this->createBuilder();
        $data = $builder->amount(1000)->saveCard(false)->toArray();

        $this->assertFalse($data['save_card']);
    }

    #[Test]
    public function it_can_set_statement_descriptor(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->statementDescriptor('ACME Corp Payment')
            ->toArray();

        $this->assertSame('ACME Corp Payment', $data['statement_descriptor']);
    }

    #[Test]
    public function it_can_set_full_receipt_configuration(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->receipt([
                'email' => true,
                'sms' => true,
                'language' => 'en',
            ])
            ->toArray();

        $this->assertTrue($data['receipt']['email']);
        $this->assertTrue($data['receipt']['sms']);
        $this->assertSame('en', $data['receipt']['language']);
    }

    #[Test]
    public function it_can_set_auto_capture_configuration(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->auto([
                'type' => 'VOID',
                'time' => 100,
            ])
            ->toArray();

        $this->assertSame('VOID', $data['auto']['type']);
        $this->assertSame(100, $data['auto']['time']);
    }

    #[Test]
    public function it_throws_exception_for_amount_below_minimum(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('Amount must be at least 10 for SAR');

        $builder = $this->createBuilder();
        $builder->amount(5)->toArray();
    }

    #[Test]
    public function it_accepts_minimum_valid_amount(): void
    {
        $builder = $this->createBuilder();
        $data = $builder->amount(10)->toArray();

        $this->assertSame(0.1, $data['amount']);
    }

    #[Test]
    public function it_can_set_full_customer_object(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->customer([
                'id' => 'cus_test_123',
                'first_name' => 'John',
                'last_name' => 'Doe',
                'email' => 'john@example.com',
            ])
            ->toArray();

        $this->assertSame('cus_test_123', $data['customer']['id']);
        $this->assertSame('John', $data['customer']['first_name']);
        $this->assertSame('Doe', $data['customer']['last_name']);
        $this->assertSame('john@example.com', $data['customer']['email']);
    }

    #[Test]
    public function it_can_chain_customer_id_after_customer_object(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->customer(['first_name' => 'John'])
            ->customerId('cus_override')
            ->toArray();

        $this->assertSame('cus_override', $data['customer']['id']);
        $this->assertSame('John', $data['customer']['first_name']);
    }

    #[Test]
    public function it_returns_immutable_copy_on_to_array(): void
    {
        $builder = $this->createBuilder();
        // KWD has 3 decimal places: 10000 / 1000 = 10.0
        $builder->amount(10000)->currency('KWD');

        $array1 = $builder->toArray();

        // Modifying returned array shouldn't affect builder
        $array1['amount'] = 200;
        $array1['currency'] = 'SAR';

        $array2 = $builder->toArray();

        // Builder should still have original values
        $this->assertSame(10.0, $array2['amount']);
        $this->assertSame('KWD', $array2['currency']);
    }

    #[Test]
    public function it_can_disable_email_receipt(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->emailReceipt(false)
            ->toArray();

        $this->assertFalse($data['receipt']['email']);
    }

    #[Test]
    public function it_can_disable_sms_receipt(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(1000)
            ->smsReceipt(false)
            ->toArray();

        $this->assertFalse($data['receipt']['sms']);
    }

    #[Test]
    public function it_can_build_complex_charge_with_all_options(): void
    {
        $builder = $this->createBuilder();
        $data = $builder
            ->amount(15075)
            ->currency('SAR')
            ->description('Premium subscription')
            ->customer([
                'first_name' => 'Ahmed',
                'last_name' => 'Ali',
                'email' => 'ahmed@example.com',
            ])
            ->customerId('cus_premium_123')
            ->withMADA()
            ->saveCard(true)
            ->statementDescriptor('ACME Premium')
            ->redirectUrl('https://example.com/success')
            ->postUrl('https://example.com/webhook')
            ->reference('ORDER-2024-001')
            ->metadata(['plan' => 'premium', 'duration' => 'yearly', 'promo_code' => 'SAVE20'])
            ->emailReceipt(true)
            ->smsReceipt(true)
            ->auto(['type' => 'VOID', 'time' => 168])
            ->toArray();

        $this->assertSame(150.75, $data['amount']);
        $this->assertSame('SAR', $data['currency']);
        $this->assertSame('Premium subscription', $data['description']);
        $this->assertSame('cus_premium_123', $data['customer']['id']);
        $this->assertSame('Ahmed', $data['customer']['first_name']);
        $this->assertSame('src_sa.mada', $data['source']['id']);
        $this->assertTrue($data['save_card']);
        $this->assertSame('ACME Premium', $data['statement_descriptor']);
        $this->assertSame('https://example.com/success', $data['redirect']['url']);
        $this->assertSame('https://example.com/webhook', $data['post']['url']);
        $this->assertSame('ORDER-2024-001', $data['reference']['transaction']);
        $this->assertSame('premium', $data['metadata']['plan']);
        $this->assertSame('SAVE20', $data['metadata']['promo_code']);
        $this->assertTrue($data['receipt']['email']);
        $this->assertTrue($data['receipt']['sms']);
        $this->assertSame('VOID', $data['auto']['type']);
    }
}
