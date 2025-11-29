<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

/**
 * Trait for handling references and description
 */
trait HasReferences
{
    public function description(string $description): static
    {
        $this->data['description'] = $description;

        return $this;
    }

    public function reference(string|array $reference): static
    {
        $existing = isset($this->data['reference']) ? $this->data['reference'] : [];

        if (is_array($reference)) {
            $this->data['reference'] = array_merge($existing, $reference);
        } else {
            $this->data['reference'] = array_merge($existing, ['transaction' => $reference]);
        }

        return $this;
    }

    public function orderReference(string $orderId): static
    {
        $existing = isset($this->data['reference']) ? $this->data['reference'] : [];
        $this->data['reference'] = array_merge($existing, ['order' => $orderId]);

        return $this;
    }
}