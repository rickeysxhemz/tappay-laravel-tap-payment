<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders;

use InvalidArgumentException;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Resources\Resource;

abstract class AbstractBuilder
{
    protected array $data = [];

    protected ?int $rawAmount = null;

    public function __construct(protected MoneyContract $money) {}

    /**
     * Set the amount
     *
     * @param int $amount Amount in smallest currency unit (e.g., 1050 for 10.50 USD, 1050 for 1.050 KWD)
     * @return self
     * @throws InvalidArgumentException
     */
    public function amount(int $amount): self
    {
        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }

        $this->rawAmount = $amount;

        return $this;
    }

    /**
     * Set the currency
     *
     * @param string $currency Currency code (e.g., 'KWD', 'SAR', 'USD')
     * @return self
     */
    public function currency(string $currency): self
    {
        $this->data['currency'] = $this->money->normalizeCurrency($currency);
        return $this;
    }

    /**
     * Set customer information
     *
     * @param array $customer Customer details array
     * @return self
     */
    public function customer(array $customer): self
    {
        $this->data['customer'] = $customer;
        return $this;
    }

    /**
     * Set customer by ID
     *
     * @param string $customerId Customer ID (e.g., 'cus_...')
     * @return self
     */
    public function customerId(string $customerId): self
    {
        $this->data['customer'] ??= [];
        $this->data['customer']['id'] = $customerId;
        return $this;
    }

    /**
     * Set description
     *
     * @param string $description Payment description
     * @return self
     */
    public function description(string $description): self
    {
        $this->data['description'] = $description;
        return $this;
    }

    /**
     * Set metadata
     *
     * @param array $metadata Key-value pairs of custom metadata
     * @return self
     */
    public function metadata(array $metadata): self
    {
        $this->data['metadata'] = $metadata;
        return $this;
    }

    /**
     * Add a single metadata item
     *
     * @param string $key Metadata key
     * @param mixed $value Metadata value
     * @return self
     */
    public function addMetadata(string $key, mixed $value): self
    {
        $this->data['metadata'] ??= [];
        $this->data['metadata'][$key] = $value;
        return $this;
    }

    /**
     * Set redirect URL
     *
     * @param string $url URL to redirect user after payment
     * @return self
     */
    public function redirectUrl(string $url): self
    {
        $this->data['redirect'] = ['url' => $url];
        return $this;
    }

    /**
     * Set post (webhook) URL
     *
     * @param string $url Webhook URL for payment notifications
     * @return self
     */
    public function postUrl(string $url): self
    {
        $this->data['post'] = ['url' => $url];
        return $this;
    }

    /**
     * Set custom reference for transaction tracking
     *
     * @param string $reference Your custom transaction reference
     * @return self
     */
    public function reference(string $reference): self
    {
        $this->data['reference'] = ['transaction' => $reference];
        return $this;
    }

    /**
     * Check if a key exists in the data
     *
     * @param string $key The key to check
     * @return bool
     */
    public function has(string $key): bool
    {
        if ($key === 'amount') {
            return $this->rawAmount !== null;
        }

        return isset($this->data[$key]);
    }

    /**
     * Get a specific data value
     *
     * @param string $key The key to retrieve
     * @param mixed $default Default value if key doesn't exist
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($key === 'amount') {
            if ($this->rawAmount === null) {
                return $default;
            }
            $currency = $this->data['currency'] ?? config('tap.currency', 'SAR');
            return $this->money->toDecimal($this->rawAmount, $currency);
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * Reset all builder data
     *
     * @return self
     */
    public function reset(): self
    {
        $this->data = [];
        $this->rawAmount = null;
        return $this;
    }

    /**
     * Get the built data array as an immutable copy
     *
     * @return array A copy of the builder data
     * @throws InvalidArgumentException
     */
    public function toArray(): array
    {
        $result = [...$this->data];

        if ($this->rawAmount !== null) {
            $currency = $result['currency'] ?? config('tap.currency', 'SAR');
            $minimum = $this->money->getMinimumAmount($currency);

            if ($this->rawAmount < $minimum) {
                throw new InvalidArgumentException(
                    "Amount must be at least {$minimum} for {$currency}"
                );
            }

            $result['amount'] = $this->money->toDecimal($this->rawAmount, $currency);
        }

        return $result;
    }

    /**
     * Build and execute the request
     *
     * @return Resource The API response as a Resource object
     */
    abstract public function create(): Resource;
}
