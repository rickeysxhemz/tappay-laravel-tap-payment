<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

/**
 * Trait for handling metadata
 */
trait HasMetadata
{
    public function metadata(array $metadata): static
    {
        $this->data['metadata'] = $metadata;

        return $this;
    }
}
