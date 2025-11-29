<?php

declare(strict_types=1);

namespace TapPay\Tap\ValueObjects;

use Illuminate\Contracts\Support\Arrayable;

/**
 * Customer value object for Tap API
 *
 * @implements Arrayable<string, mixed>
 */
readonly class Customer implements Arrayable
{
    public function __construct(
        public ?string $id = null,
        public ?string $firstName = null,
        public ?string $lastName = null,
        public ?string $email = null,
        public ?Phone $phone = null,
        public ?string $middleName = null,
        public ?string $nationality = null
    ) {}

    /**
     * Create a customer with just an ID (for existing customers)
     */
    public static function fromId(string $id): self
    {
        return new self(id: $id);
    }

    /**
     * Create a new customer with details
     */
    public static function make(
        ?string $firstName = null,
        ?string $lastName = null,
        ?string $email = null,
        ?Phone $phone = null
    ): self {
        return new self(
            firstName: $firstName,
            lastName: $lastName,
            email: $email,
            phone: $phone
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'id' => $this->id,
            'first_name' => $this->firstName,
            'middle_name' => $this->middleName,
            'last_name' => $this->lastName,
            'email' => $this->email,
            'phone' => $this->phone?->toArray(),
            'nationality' => $this->nationality,
        ], static fn ($value): bool => $value !== null);
    }
}