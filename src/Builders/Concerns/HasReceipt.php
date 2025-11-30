<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

/**
 * Trait for handling receipt settings
 */
trait HasReceipt
{
    /**
     * @param  array<string, mixed>  $receipt
     */
    public function receipt(array $receipt): static
    {
        $existing = $this->getExistingReceipt();
        $this->data['receipt'] = array_merge($existing, $receipt);

        return $this;
    }

    public function emailReceipt(bool $email = true): static
    {
        $existing = $this->getExistingReceipt();
        $this->data['receipt'] = array_merge($existing, ['email' => $email]);

        return $this;
    }

    public function smsReceipt(bool $sms = true): static
    {
        $existing = $this->getExistingReceipt();
        $this->data['receipt'] = array_merge($existing, ['sms' => $sms]);

        return $this;
    }

    public function withReceipts(): static
    {
        return $this->emailReceipt()->smsReceipt();
    }

    /**
     * @return array<string, mixed>
     */
    private function getExistingReceipt(): array
    {
        if (isset($this->data['receipt']) && is_array($this->data['receipt'])) {
            /** @var array<string, mixed> */
            return $this->data['receipt'];
        }

        return [];
    }
}
