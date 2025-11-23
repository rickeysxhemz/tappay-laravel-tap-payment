<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Http\Client;

abstract class AbstractService
{
    /**
     * Create a new service instance
     *
     * @param Client $client HTTP client for API communication
     */
    public function __construct(
        protected Client $client
    ) {}

    /**
     * Get the endpoint for this service
     *
     * @return string
     */
    abstract protected function getEndpoint(): string;
}
