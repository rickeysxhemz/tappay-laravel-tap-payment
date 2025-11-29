<?php

declare(strict_types=1);

namespace TapPay\Tap\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * Destination value object for marketplace payment splitting
 *
 * @implements Arrayable<string, mixed>
 */
readonly class Destination implements Arrayable
{
    public function __construct(
        public string $id,
        public float $amount,
        public ?string $currency = null
    ) {
        if (empty($this->id)) {
            throw new InvalidArgumentException('Destination ID cannot be empty');
        }

        if ($this->amount <= 0) {
            throw new InvalidArgumentException('Destination amount must be positive');
        }
    }

    /**
     * Create a destination
     */
    public static function make(string $id, float $amount, ?string $currency = null): self
    {
        return new self($id, $amount, $currency);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $data = [
            'id' => $this->id,
            'amount' => $this->amount,
        ];

        if ($this->currency !== null) {
            $data['currency'] = $this->currency;
        }

        return $data;
    }
}