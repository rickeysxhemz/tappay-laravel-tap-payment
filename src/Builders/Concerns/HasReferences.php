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

    /**
     * @param  string|array<string, mixed>  $reference
     */
    public function reference(string|array $reference): static
    {
        $existing = $this->getExistingReference();

        if (is_array($reference)) {
            $this->data['reference'] = array_merge($existing, $reference);
        } else {
            $this->data['reference'] = array_merge($existing, ['transaction' => $reference]);
        }

        return $this;
    }

    public function orderReference(string $orderId): static
    {
        $existing = $this->getExistingReference();
        $this->data['reference'] = array_merge($existing, ['order' => $orderId]);

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    private function getExistingReference(): array
    {
        if (isset($this->data['reference']) && is_array($this->data['reference'])) {
            /** @var array<string, mixed> */
            return $this->data['reference'];
        }

        return [];
    }
}
