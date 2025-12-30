<?php

declare(strict_types=1);

namespace TapPay\Tap;

use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Http\Client;
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

/**
 * Main Tap Payments SDK class
 *
 * Provides access to all Tap Payments API services.
 * Octane-safe: no static state, no service caching.
 */
class Tap
{
    public function __construct(
        protected Client $client,
        protected MoneyContract $money
    ) {}

    /**
     * Check if routes should be registered (config-based for Octane safety)
     */
    public static function registersRoutes(): bool
    {
        return (bool) config('tap.register_routes', true);
    }

    public function charges(): ChargeService
    {
        return new ChargeService($this->client, $this->money);
    }

    public function customers(): CustomerService
    {
        return new CustomerService($this->client);
    }

    public function refunds(): RefundService
    {
        return new RefundService($this->client);
    }

    public function authorizations(): AuthorizeService
    {
        return new AuthorizeService($this->client, $this->money);
    }

    public function tokens(): TokenService
    {
        return new TokenService($this->client);
    }

    public function cards(): CardService
    {
        return new CardService($this->client);
    }

    public function invoices(): InvoiceService
    {
        return new InvoiceService($this->client);
    }

    public function subscriptions(): SubscriptionService
    {
        return new SubscriptionService($this->client);
    }

    /**
     * Marketplace: Sub-merchant management
     */
    public function merchants(): MerchantService
    {
        return new MerchantService($this->client);
    }

    /**
     * Marketplace: Payment split destinations
     */
    public function destinations(): DestinationService
    {
        return new DestinationService($this->client);
    }

    /**
     * Marketplace: Merchant settlement/payout tracking
     */
    public function payouts(): PayoutService
    {
        return new PayoutService($this->client);
    }
}
