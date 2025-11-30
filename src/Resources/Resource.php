<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use Carbon\Carbon;
use Exception;
use TapPay\Tap\Exceptions\InvalidDateTimeException;

use function is_array;
use function is_numeric;
use function is_string;
use function str_starts_with;

/**
 * Base resource class for all Tap API responses
 */
abstract class Resource
{
    /**
     * Create a new resource instance
     *
     * @param  array<string, mixed>  $attributes  Resource attributes from API
     */
    public function __construct(
        protected array $attributes
    ) {}

    /**
     * Get the resource ID
     */
    public function id(): string
    {
        $id = $this->attributes['id'] ?? '';

        return is_string($id) ? $id : '';
    }

    /**
     * Get the ID prefix for this resource type (e.g., 'chg_', 'cus_', 'ref_')
     */
    abstract protected function getIdPrefix(): string;

    /**
     * Check if the resource ID has a valid format
     */
    public function hasValidId(): bool
    {
        $id = $this->id();

        return $id !== '' && str_starts_with($id, $this->getIdPrefix());
    }

    /**
     * Get metadata
     *
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        $metadata = $this->attributes['metadata'] ?? [];

        if (is_array($metadata)) {
            /** @var array<string, mixed> */
            return $metadata;
        }

        return [];
    }

    /**
     * Get all attributes
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute by key with support for dot notation
     */
    public function get(string $key, string|int|float|bool|array|null $default = null): string|int|float|bool|array|null
    {
        /** @var string|int|float|bool|array<mixed>|null */
        return data_get($this->attributes, $key, $default);
    }

    /**
     * Get a string attribute with type safety
     */
    protected function getString(string $key, string $default = ''): string
    {
        $value = $this->attributes[$key] ?? $default;

        return is_string($value) ? $value : $default;
    }

    /**
     * Get a float attribute with type safety
     */
    protected function getFloat(string $key, float $default = 0.0): float
    {
        $value = $this->attributes[$key] ?? $default;

        return is_numeric($value) ? (float) $value : $default;
    }

    /**
     * Get an int attribute with type safety
     */
    protected function getInt(string $key, int $default = 0): int
    {
        $value = $this->attributes[$key] ?? $default;

        return is_numeric($value) ? (int) $value : $default;
    }

    /**
     * Get a nullable string attribute with type safety
     */
    protected function getNullableString(string $key): ?string
    {
        $value = $this->attributes[$key] ?? null;

        return is_string($value) ? $value : null;
    }

    /**
     * Get a nullable int attribute with type safety
     */
    protected function getNullableInt(string $key): ?int
    {
        $value = $this->attributes[$key] ?? null;

        return is_numeric($value) ? (int) $value : null;
    }

    /**
     * Check if an attribute exists
     *
     * @param  string  $key  Attribute key
     */
    public function has(string $key): bool
    {
        return data_get($this->attributes, $key) !== null;
    }

    /**
     * Check if the resource has any data
     */
    public function isEmpty(): bool
    {
        return empty($this->attributes);
    }

    /**
     * Magic getter for attributes
     */
    public function __get(string $key): string|int|float|bool|array|null
    {
        return $this->get($key);
    }

    /**
     * Parse a timestamp or date string into a Carbon instance
     *
     * @throws InvalidDateTimeException
     */
    protected function parseDateTime(int|string $value): Carbon
    {
        try {
            return Carbon::parse($value);
        } catch (Exception) {
            InvalidDateTimeException::invalidValue($value);
        }
    }
}
