<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

use TapPay\Tap\ValueObjects\Destination;

/**
 * Trait for handling marketplace payment splitting
 */
trait HasDestinations
{
    /**
     * @param  array<array<string, mixed>|Destination>  $destinations
     */
    public function destinations(array $destinations): static
    {
        $mappedDestinations = [];
        foreach ($destinations as $d) {
            $mappedDestinations[] = $d instanceof Destination ? $d->toArray() : $d;
        }

        $this->data['destinations'] = ['destination' => $mappedDestinations];

        return $this;
    }
}
