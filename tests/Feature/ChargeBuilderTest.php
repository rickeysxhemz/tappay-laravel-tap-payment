<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Enums\SourceObject;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Tests\TestCase;

class ChargeBuilderTest extends TestCase
{
    protected ChargeService $chargeService;
    protected MockHandler $mockHandler;

    protected function setUp(): void
    {
        parent::setUp();

        $this->mockHandler = new MockHandler();
        $handlerStack = HandlerStack::create($this->mockHandler);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $httpClient = new Client(config('tap.secret_key'));
        $reflection = new \ReflectionClass($httpClient);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($httpClient, $guzzleClient);

        $this->chargeService = new ChargeService($httpClient);
    }
    #[Test]
    public function it_builds_charge_with_fluent_interface(): void
    {
        $builder = new ChargeBuilder($this->chargeService);

        $data = $builder
            ->amount(100.50)
            ->currency('KWD')
            ->description('Test payment')
            ->customerId('cus_test_123')
            ->withCard()
            ->saveCard()
            ->redirectUrl('https://example.com/success')
            ->postUrl('https://example.com/webhook')
            ->metadata(['order_id' => '12345'])
            ->toArray();

        $this->assertSame(100.50, $data['amount']);
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
        $builder = new ChargeBuilder($this->chargeService);

        // Test KNET
        $data = $builder->amount(10)->withKNET()->toArray();
        $this->assertSame('src_kw.knet', $data['source']['id']);

        // Test MADA
        $builder = new ChargeBuilder($this->chargeService);
        $data = $builder->amount(10)->withMADA()->toArray();
        $this->assertSame('src_sa.mada', $data['source']['id']);

        // Test Token
        $builder = new ChargeBuilder($this->chargeService);
        $data = $builder->amount(10)->withToken('tok_abc123')->toArray();
        $this->assertSame('tok_abc123', $data['source']['id']);
    }
    #[Test]
    public function it_can_add_metadata_incrementally(): void
    {
        $builder = new ChargeBuilder($this->chargeService);

        $data = $builder
            ->amount(10)
            ->addMetadata('order_id', '123')
            ->addMetadata('user_id', '456')
            ->addMetadata('invoice', 'INV-001')
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
        $builder = new ChargeBuilder($this->chargeService);

        $data = $builder
            ->amount(10)
            ->emailReceipt(true)
            ->smsReceipt(true)
            ->toArray();

        $this->assertTrue($data['receipt']['email']);
        $this->assertTrue($data['receipt']['sms']);
    }
    #[Test]
    public function it_can_capture_authorization(): void
    {
        $builder = new ChargeBuilder($this->chargeService);

        $data = $builder
            ->amount(50)
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

        $charge = (new ChargeBuilder($this->chargeService))
            ->amount(25)
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

        $builder = new ChargeBuilder($this->chargeService);
        $data = $builder->amount(10)->toArray();

        $this->assertSame('KWD', $data['currency']);
    }
    #[Test]
    public function it_can_override_default_currency(): void
    {
        config(['tap.currency' => 'KWD']);

        $builder = new ChargeBuilder($this->chargeService);
        $data = $builder
            ->amount(10)
            ->currency('SAR')
            ->toArray();

        $this->assertSame('SAR', $data['currency']);
    }
    #[Test]
    public function it_accepts_source_enum_or_string(): void
    {
        $builder = new ChargeBuilder($this->chargeService);

        // Test with enum
        $data = $builder->amount(10)->source(SourceObject::SRC_BENEFIT)->toArray();
        $this->assertSame('src_bh.benefit', $data['source']['id']);

        // Test with string
        $builder = new ChargeBuilder($this->chargeService);
        $data = $builder->amount(10)->source('src_custom')->toArray();
        $this->assertSame('src_custom', $data['source']['id']);
    }
}