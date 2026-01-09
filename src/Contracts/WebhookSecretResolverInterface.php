<?php

declare(strict_types=1);

namespace TapPay\Tap\Contracts;

interface WebhookSecretResolverInterface
{
    /**
     * @param  array<string, mixed>  $payload
     * @return string|null Secret to use, or null for default
     */
    public function resolve(array $payload): ?string;
}
