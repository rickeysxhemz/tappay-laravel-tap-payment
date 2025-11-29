<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

/**
 * Trait for handling receipt settings
 */
trait HasReceipt
{
    public function receipt(array $receipt): static
    {
        $existing = isset($this->data['receipt']) ? $this->data['receipt'] : [];
        $this->data['receipt'] = array_merge($existing, $receipt);

        return $this;
    }

    public function emailReceipt(bool $email = true): static
    {
        $existing = isset($this->data['receipt']) ? $this->data['receipt'] : [];
        $this->data['receipt'] = array_merge($existing, ['email' => $email]);

        return $this;
    }

    public function smsReceipt(bool $sms = true): static
    {
        $existing = isset($this->data['receipt']) ? $this->data['receipt'] : [];
        $this->data['receipt'] = array_merge($existing, ['sms' => $sms]);

        return $this;
    }

    public function withReceipts(): static
    {
        return $this->emailReceipt()->smsReceipt();
    }
}