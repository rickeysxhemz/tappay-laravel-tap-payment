<?php

declare(strict_types=1);

namespace TapPay\Tap\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;
use InvalidArgumentException;

/**
 * Phone number value object for Tap API
 *
 * @implements Arrayable<string, string>
 */
readonly class Phone implements Arrayable
{
    public function __construct(
        public string $countryCode,
        public string $number
    ) {
        if (empty($this->countryCode)) {
            throw new InvalidArgumentException('Country code cannot be empty');
        }

        if (empty($this->number)) {
            throw new InvalidArgumentException('Phone number cannot be empty');
        }
    }

    /**
     * Create from country code and number
     */
    public static function make(string $countryCode, string $number): self
    {
        return new self($countryCode, $number);
    }

    /**
     * @return array{country_code: string, number: string}
     */
    public function toArray(): array
    {
        return [
            'country_code' => $this->countryCode,
            'number' => $this->number,
        ];
    }
}
