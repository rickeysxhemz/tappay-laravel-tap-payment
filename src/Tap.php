<?php

declare(strict_types=1);

namespace TapPay\Tap;

use TapPay\Tap\Http\Client;
use TapPay\Tap\Services\AuthorizeService;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Services\CustomerService;
use TapPay\Tap\Services\RefundService;
use TapPay\Tap\Services\TokenService;

/**
 * Main Tap Payments SDK class
 *
 * Provides a fluent interface to access all Tap Payments API services.
 * This class is registered as a singleton in the service container.
 *
 * Services are created fresh on each call to prevent any possibility of
 * data leakage in Laravel Octane environments. The HTTP client is cached
 * as it contains only application-level configuration.
 *
 * @example
 * ```php
 * use TapPay\Tap\Facades\Tap;
 *
 * // Create a charge
 * $charge = Tap::charges()->create([
 *     'amount' => 10.5,
 *     'currency' => 'KWD',
 *     'source' => ['id' => 'src_all'],
 *     'redirect' => ['url' => 'https://example.com/success'],
 * ]);
 *
 * // Retrieve a customer
 * $customer = Tap::customers()->retrieve('cus_xxx');
 * ```
 *
 * @see Facades\Tap
 */
class Tap
{
    /**
     * HTTP client instance (cached, contains only app-level config)
     */
    protected Client $client;

    /**
     * Create a new Tap instance
     *
     * @param string|null $secretKey Optional secret key. If not provided, uses config('tap.secret_key')
     * @throws \RuntimeException If secret key is not configured
     * @throws \InvalidArgumentException If secret key is empty
     */
    public function __construct(?string $secretKey = null)
    {
        $secretKey = $secretKey ?? config('tap.secret_key');
        $this->client = new Client($secretKey);
    }

    /**
     * Get a new ChargeService instance
     *
     * Creates a fresh instance on each call to prevent data leakage in Octane.
     * This is intentional and has minimal performance impact as services are lightweight.
     *
     * @return ChargeService
     */
    public function charges(): ChargeService
    {
        return new ChargeService($this->client);
    }

    /**
     * Get a new CustomerService instance
     *
     * Creates a fresh instance on each call to prevent data leakage in Octane.
     * This is intentional and has minimal performance impact as services are lightweight.
     *
     * @return CustomerService
     */
    public function customers(): CustomerService
    {
        return new CustomerService($this->client);
    }

    /**
     * Get a new RefundService instance
     *
     * Creates a fresh instance on each call to prevent data leakage in Octane.
     * This is intentional and has minimal performance impact as services are lightweight.
     *
     * @return RefundService
     */
    public function refunds(): RefundService
    {
        return new RefundService($this->client);
    }

    /**
     * Get a new AuthorizeService instance
     *
     * Creates a fresh instance on each call to prevent data leakage in Octane.
     * This is intentional and has minimal performance impact as services are lightweight.
     *
     * @return AuthorizeService
     */
    public function authorizations(): AuthorizeService
    {
        return new AuthorizeService($this->client);
    }

    /**
     * Get a new TokenService instance
     *
     * Creates a fresh instance on each call to prevent data leakage in Octane.
     * This is intentional and has minimal performance impact as services are lightweight.
     *
     * @return TokenService
     */
    public function tokens(): TokenService
    {
        return new TokenService($this->client);
    }

    /**
     * Get the HTTP client instance
     *
     * @return Client
     */
    public function getClient(): Client
    {
        return $this->client;
    }
}