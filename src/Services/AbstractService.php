<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Http\Client;

abstract class AbstractService
{
    protected Client $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Get the endpoint for this service
     */
    abstract protected function getEndpoint(): string;
}
