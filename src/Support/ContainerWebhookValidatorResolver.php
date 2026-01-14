<?php

declare(strict_types=1);

namespace TapPay\Tap\Support;

use TapPay\Tap\Contracts\WebhookValidatorResolver;
use TapPay\Tap\Webhooks\WebhookValidator;

final class ContainerWebhookValidatorResolver implements WebhookValidatorResolver
{
    public function resolve(): WebhookValidator
    {
        return app(WebhookValidator::class);
    }
}
