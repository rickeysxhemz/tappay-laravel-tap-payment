<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use Carbon\Carbon;
use Exception;
use TapPay\Tap\Exceptions\InvalidDateTimeException;

use function str_starts_with;

/**
 * Base resource class for all Tap API responses
 */
abstract class Resource
{
    /**
     * Create a new resource instance
     *
     * @param  array  $attributes  Resource attributes from API
     */
    public function __construct(
        protected array $attributes
    ) {}

    /**
     * Get the resource ID
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
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
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

    /**
     * Get all attributes
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
        return data_get($this->attributes, $key, $default);
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
