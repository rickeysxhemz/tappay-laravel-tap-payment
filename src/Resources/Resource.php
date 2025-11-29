<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use DateTime;

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
     * Get the resource ID
     *
     * @return string
     */
    public function id(): string
    {
        return $this->attributes['id'] ?? '';
    }

    /**
     * Get the ID prefix for this resource type (e.g., 'chg_', 'cus_', 'ref_')
     *
     * @return string
     */
    abstract protected function getIdPrefix(): string;

    /**
     * Check if the resource ID has a valid format
     *
     * @return bool
     */
    public function hasValidId(): bool
    {
        $id = $this->id();

        return $id !== '' && str_starts_with($id, $this->getIdPrefix());
    }

    /**
     * Get metadata
     *
     * @return array
     */
    public function metadata(): array
    {
        return $this->attributes['metadata'] ?? [];
    }

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

    /**
     * Parse a timestamp or date string into a DateTime object
     *
     * @param int|string $value Unix timestamp or date string
     * @return DateTime|null
     */
    protected function parseDateTime(int|string $value): ?DateTime
    {
        try {
            if (is_numeric($value)) {
                return (new DateTime())->setTimestamp((int) $value);
            }
            return new DateTime($value);
        } catch (\Exception) {
            return null;
        }
    }
}