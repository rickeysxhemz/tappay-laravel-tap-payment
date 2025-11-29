<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders\Concerns;

use InvalidArgumentException;
use TapPay\Tap\Enums\SourceObject;
use TapPay\Tap\ValueObjects\Source;

use function str_starts_with;

/**
 * Trait for handling payment sources
 */
trait HasSource
{
    public function source(string|SourceObject|Source $source): static
    {
        if ($source instanceof Source) {
            $this->data['source'] = $source->toArray();
        } else {
            $sourceValue = $source instanceof SourceObject ? $source->value : $source;
            $this->data['source'] = ['id' => $sourceValue];
        }

        return $this;
    }

    public function withCard(): static
    {
        return $this->source(SourceObject::SRC_CARD);
    }

    public function withAllMethods(): static
    {
        return $this->source(SourceObject::SRC_ALL);
    }

    public function withKNET(): static
    {
        return $this->source(SourceObject::SRC_KNET);
    }

    public function withKFAST(): static
    {
        return $this->source(SourceObject::SRC_KFAST);
    }

    public function withMADA(): static
    {
        return $this->source(SourceObject::SRC_MADA);
    }

    public function withBenefit(): static
    {
        return $this->source(SourceObject::SRC_BENEFIT);
    }

    public function withOmanNet(): static
    {
        return $this->source(SourceObject::SRC_OMANNET);
    }

    public function withNAPS(): static
    {
        return $this->source(SourceObject::SRC_NAPS);
    }

    public function withFawry(): static
    {
        return $this->source(SourceObject::SRC_FAWRY);
    }

    public function withSTCPay(): static
    {
        return $this->source(SourceObject::SRC_STC_PAY);
    }

    public function withTabby(): static
    {
        return $this->source(SourceObject::SRC_TABBY);
    }

    public function withToken(string $tokenId): static
    {
        if (! str_starts_with($tokenId, 'tok_')) {
            throw new InvalidArgumentException('Token ID must start with "tok_"');
        }

        return $this->source($tokenId);
    }

    public function captureAuthorization(string $authId): static
    {
        if (! str_starts_with($authId, 'auth_')) {
            throw new InvalidArgumentException('Authorization ID must start with "auth_"');
        }

        return $this->source($authId);
    }
}
