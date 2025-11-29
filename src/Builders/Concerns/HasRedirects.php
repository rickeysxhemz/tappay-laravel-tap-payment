<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

use InvalidArgumentException;

/**
 * Trait for handling redirect and post URLs
 */
trait HasRedirects
{
    public function redirectUrl(string $url): static
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid redirect URL format');
        }

        $this->data['redirect'] = ['url' => $url];

        return $this;
    }

    public function postUrl(string $url): static
    {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException('Invalid webhook URL format');
        }

        $this->data['post'] = ['url' => $url];

        return $this;
    }
}