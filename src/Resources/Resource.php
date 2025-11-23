<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

/**
 * Base resource class for all Tap API responses
 */
abstract class Resource
{
    /**
     * Create a new resource instance
     *
     * @param array $attributes Resource attributes from API
     */
    public function __construct(
        protected array $attributes
    ) {}

    /**
     * Get all attributes
     *
     * @return array
     */
    public function toArray(): array
    {
        return $this->attributes;
    }

    /**
     * Get an attribute by key with support for dot notation
     *
     * @param string $key Attribute key (supports dot notation)
     * @param string|int|float|bool|array|null $default Default value if key doesn't exist
     * @return string|int|float|bool|array|null
     */
    public function get(string $key, string|int|float|bool|array|null $default = null): string|int|float|bool|array|null
    {
        return data_get($this->attributes, $key, $default);
    }

    /**
     * Check if an attribute exists
     *
     * @param string $key Attribute key
     * @return bool
     */
    public function has(string $key): bool
    {
        return data_get($this->attributes, $key) !== null;
    }

    /**
     * Check if the resource has any data
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->attributes);
    }

    /**
     * Magic getter for attributes
     *
     * @param string $key Attribute key
     * @return string|int|float|bool|array|null
     */
    public function __get(string $key): string|int|float|bool|array|null
    {
        return $this->get($key);
    }
}