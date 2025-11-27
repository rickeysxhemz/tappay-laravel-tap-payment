<?php

declare(strict_types=1);

namespace TapPay\Tap;

use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Services\AuthorizeService;
use TapPay\Tap\Services\CardService;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Services\CustomerService;
use TapPay\Tap\Services\InvoiceService;
use TapPay\Tap\Services\RefundService;
use TapPay\Tap\Services\SubscriptionService;
use TapPay\Tap\Services\TokenService;

/**
 * Main Tap Payments SDK class
 *
 * Provides a fluent interface to access all Tap Payments API services.
 */
class Tap
{
    /**
     * Indicates if Tap routes will be registered.
     */
    public static bool $registersRoutes = true;

    protected Client $client;

    protected MoneyContract $money;

    /**
     * @var array<string, object>
     */
    protected array $services = [];

    /**
     * Configure Tap to not register its routes.
     */
    public static function ignoreRoutes(): void
    {
        static::$registersRoutes = false;
    }

    /**
     * Create a new Tap instance
     *
     * @param Client|null $client Optional HTTP client. Falls back to container-resolved singleton.
     * @param MoneyContract|null $money Optional Money instance. Falls back to container-resolved singleton.
     */
    public function __construct(?Client $client = null, ?MoneyContract $money = null)
    {
        $this->client = $client ?? app(Client::class);
        $this->money = $money ?? app(MoneyContract::class);
    }

    /**
     * Get the ChargeService instance
     */
    public function charges(): ChargeService
    {
        return $this->services['charges'] ??= new ChargeService($this->client, $this->money);
    }

    /**
     * Get the CustomerService instance
     */
    public function customers(): CustomerService
    {
        return $this->services['customers'] ??= new CustomerService($this->client);
    }

    /**
     * Get the RefundService instance
     */
    public function refunds(): RefundService
    {
        return $this->services['refunds'] ??= new RefundService($this->client);
    }

    /**
     * Get the AuthorizeService instance
     */
    public function authorizations(): AuthorizeService
    {
        return $this->services['authorizations'] ??= new AuthorizeService($this->client);
    }

    /**
     * Get the TokenService instance
     */
    public function tokens(): TokenService
    {
        return $this->services['tokens'] ??= new TokenService($this->client);
    }

    /**
     * Get the CardService instance
     */
    public function cards(): CardService
    {
        return $this->services['cards'] ??= new CardService($this->client);
    }

    /**
     * Get the InvoiceService instance
     */
    public function invoices(): InvoiceService
    {
        return $this->services['invoices'] ??= new InvoiceService($this->client);
    }

    /**
     * Get the SubscriptionService instance
     */
    public function subscriptions(): SubscriptionService
    {
        return $this->services['subscriptions'] ??= new SubscriptionService($this->client);
    }

    /**
     * Get the HTTP client instance
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}