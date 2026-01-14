<?php

declare(strict_types=1);

namespace TapPay\Tap\Support;

use TapPay\Tap\Contracts\ClientResolver;
use TapPay\Tap\Http\Client;

final class ContainerClientResolver implements ClientResolver
{
    public function resolve(): Client
    {
        return app(Client::class);
    }
}
