<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Services\AuthorizeService;
use TapPay\Tap\Services\CardService;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Services\CustomerService;
use TapPay\Tap\Services\DestinationService;
use TapPay\Tap\Services\InvoiceService;
use TapPay\Tap\Services\MerchantService;
use TapPay\Tap\Services\PayoutService;
use TapPay\Tap\Services\RefundService;
use TapPay\Tap\Services\SubscriptionService;
use TapPay\Tap\Services\TokenService;
use TapPay\Tap\Tap;
use TapPay\Tap\Tests\TestCase;

class TapClassTest extends TestCase
{
    protected Tap $tap;

    protected function setUp(): void
    {
        parent::setUp();

        $client = $this->mockHttpClient();
        $money = app(MoneyContract::class);
        $this->tap = new Tap($client, $money);
    }

    #[Test]
    public function it_returns_charge_service(): void
    {
        $service = $this->tap->charges();

        $this->assertInstanceOf(ChargeService::class, $service);
    }

    #[Test]
    public function it_returns_customer_service(): void
    {
        $service = $this->tap->customers();

        $this->assertInstanceOf(CustomerService::class, $service);
    }

    #[Test]
    public function it_returns_refund_service(): void
    {
        $service = $this->tap->refunds();

        $this->assertInstanceOf(RefundService::class, $service);
    }

    #[Test]
    public function it_returns_authorization_service(): void
    {
        $service = $this->tap->authorizations();

        $this->assertInstanceOf(AuthorizeService::class, $service);
    }

    #[Test]
    public function it_returns_token_service(): void
    {
        $service = $this->tap->tokens();

        $this->assertInstanceOf(TokenService::class, $service);
    }

    #[Test]
    public function it_returns_card_service(): void
    {
        $service = $this->tap->cards();

        $this->assertInstanceOf(CardService::class, $service);
    }

    #[Test]
    public function it_returns_invoice_service(): void
    {
        $service = $this->tap->invoices();

        $this->assertInstanceOf(InvoiceService::class, $service);
    }

    #[Test]
    public function it_returns_subscription_service(): void
    {
        $service = $this->tap->subscriptions();

        $this->assertInstanceOf(SubscriptionService::class, $service);
    }

    #[Test]
    public function it_returns_merchant_service(): void
    {
        $service = $this->tap->merchants();

        $this->assertInstanceOf(MerchantService::class, $service);
    }

    #[Test]
    public function it_returns_destination_service(): void
    {
        $service = $this->tap->destinations();

        $this->assertInstanceOf(DestinationService::class, $service);
    }

    #[Test]
    public function it_returns_payout_service(): void
    {
        $service = $this->tap->payouts();

        $this->assertInstanceOf(PayoutService::class, $service);
    }

    #[Test]
    public function it_creates_new_service_instance_each_time(): void
    {
        $service1 = $this->tap->charges();
        $service2 = $this->tap->charges();

        $this->assertNotSame($service1, $service2);
    }

    #[Test]
    public function registers_routes_returns_true_by_default(): void
    {
        config(['tap.register_routes' => true]);

        $this->assertTrue(Tap::registersRoutes());
    }

    #[Test]
    public function registers_routes_returns_false_when_disabled(): void
    {
        config(['tap.register_routes' => false]);

        $this->assertFalse(Tap::registersRoutes());
    }

    #[Test]
    public function registers_routes_returns_true_when_config_not_set(): void
    {
        config(['tap.register_routes' => null]);

        $this->assertFalse(Tap::registersRoutes());
    }
}
