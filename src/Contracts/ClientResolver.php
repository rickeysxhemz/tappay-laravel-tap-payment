<?php

declare(strict_types=1);

namespace TapPay\Tap\Contracts;

use TapPay\Tap\Http\Client;

interface ClientResolver
{
    /**
     * Resolve the HTTP client.
     */
    public function resolve(): Client;
}
