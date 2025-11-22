<?php

declare(strict_types=1);

namespace TapPay\Tap;

use TapPay\Tap\Http\Client;
use TapPay\Tap\Services\ChargeService;
use TapPay\Tap\Services\CustomerService;
use TapPay\Tap\Services\RefundService;
use TapPay\Tap\Services\AuthorizeService;
use TapPay\Tap\Services\TokenService;

class Tap
{
    protected Client $client;

    public function __construct(?string $secretKey = null)
    {
        $secretKey = $secretKey ?? config('tap.secret_key');
        $this->client = new Client($secretKey);
    }

    /**
     * Get the ChargeService instance
     */
    public function charges(): ChargeService
    {
        return new ChargeService($this->client);
    }

    /**
     * Get the CustomerService instance
     */
    public function customers(): CustomerService
    {
        return new CustomerService($this->client);
    }

    /**
     * Get the RefundService instance
     */
    public function refunds(): RefundService
    {
        return new RefundService($this->client);
    }

    /**
     * Get the AuthorizeService instance
     */
    public function authorizations(): AuthorizeService
    {
        return new AuthorizeService($this->client);
    }

    /**
     * Get the TokenService instance
     */
    public function tokens(): TokenService
    {
        return new TokenService($this->client);
    }

    /**
     * Get the HTTP client instance
     */
    public function getClient(): Client
    {
        return $this->client;
    }

    /**
     * Set a custom HTTP client
     */
    public function setClient(Client $client): self
    {
        $this->client = $client;
        return $this;
    }
}
