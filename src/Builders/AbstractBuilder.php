<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Traits\Conditionable;
use InvalidArgumentException;
use TapPay\Tap\Builders\Concerns\HasBuilderCapabilities;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Resources\Resource;

/**
 * Abstract base builder for Tap API requests
 *
 * @implements Arrayable<string, mixed>
 */
abstract class AbstractBuilder implements Arrayable, Jsonable
{
    use Conditionable;
    use HasBuilderCapabilities;

    /**
     * @var array<string, mixed>
     */
    protected array $data = [];

    public function __construct(
        protected readonly MoneyContract $money
    ) {}

    /**
     * Check if a key exists in the data
     */
    public function has(string $key): bool
    {
        if ($key === 'amount') {
            return $this->hasAmount();
        }

        return isset($this->data[$key]);
    }

    /**
     * Get a specific data value
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($key === 'amount') {
            if (!$this->hasAmount()) {
                return $default;
            }
            $currency = $this->data['currency'] ?? config('tap.currency', 'SAR');

            return $this->money->toDecimal($this->getRawAmount(), $currency);
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * Reset all builder data
     */
    public function reset(): static
    {
        $this->data = [];
        $this->resetAmount();

        return $this;
    }

    /**
     * Validate required fields before building
     *
     * @param array<string> $requiredFields Fields required for this builder
     * @throws InvalidArgumentException
     */
    protected function validateRequired(array $requiredFields = []): void
    {
        $missing = [];

        foreach ($requiredFields as $field) {
            if (!$this->has($field)) {
                $missing[] = $field;
            }
        }

        if ($missing !== []) {
            throw new InvalidArgumentException(
                'Missing required fields: ' . implode(', ', $missing)
            );
        }
    }

    /**
     * Get the built data array
     *
     * @return array<string, mixed>
     * @throws InvalidArgumentException
     */
    public function toArray(): array
    {
        $result = [...$this->data];

        if ($this->hasAmount()) {
            $this->validateMinimumAmount();
            $result['amount'] = $this->getFormattedAmount();
        }

        return $result;
    }

    /**
     * Get the JSON representation of the builder data
     *
     * @param int $options JSON encoding options
     * @throws \JsonException
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options | JSON_THROW_ON_ERROR);
    }

    /**
     * Build and execute the request
     *
     * @return Resource The API response as a Resource object
     */
    abstract public function create(): Resource;
}