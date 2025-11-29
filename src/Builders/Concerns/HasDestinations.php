<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

use TapPay\Tap\ValueObjects\Destination;

/**
 * Trait for handling marketplace payment splitting
 */
trait HasDestinations
{
    public function destinations(array $destinations): static
    {
        $mappedDestinations = array_map(
            static fn (array|Destination $d): array => $d instanceof Destination ? $d->toArray() : $d,
            $destinations
        );

        $this->data['destinations'] = ['destination' => $mappedDestinations];

        return $this;
    }
}
