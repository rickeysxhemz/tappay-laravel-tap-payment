<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

use TapPay\Tap\Contracts\WebhookSecretResolverInterface;

final class ConfigWebhookSecretResolver implements WebhookSecretResolverInterface
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function resolve(array $payload): ?string
    {
        return null;
    }
}
