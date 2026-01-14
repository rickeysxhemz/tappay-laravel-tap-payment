<?php

declare(strict_types=1);

namespace TapPay\Tap\Contracts;

use TapPay\Tap\Webhooks\WebhookValidator;

interface WebhookValidatorResolver
{
    /**
     * Resolve the webhook validator.
     */
    public function resolve(): WebhookValidator;
}
