<?php

declare(strict_types=1);

namespace TapPay\Tap\Builders;

abstract class AbstractBuilder
{
    protected array $data = [];

    /**
     * Set the amount
     */
    public function amount(float $amount): self
    {
        $this->data['amount'] = $amount;
        return $this;
    }

    /**
     * Set the currency
     */
    public function currency(string $currency): self
    {
        $this->data['currency'] = $currency;
        return $this;
    }

    /**
     * Set customer information
     */
    public function customer(array $customer): self
    {
        $this->data['customer'] = $customer;
        return $this;
    }

    /**
     * Set customer by ID
     */
    public function customerId(string $customerId): self
    {
        $this->data['customer'] = ['id' => $customerId];
        return $this;
    }

    /**
     * Set description
     */
    public function description(string $description): self
    {
        $this->data['description'] = $description;
        return $this;
    }

    /**
     * Set metadata
     */
    public function metadata(array $metadata): self
    {
        $this->data['metadata'] = $metadata;
        return $this;
    }

    /**
     * Add a single metadata item
     */
    public function addMetadata(string $key, $value): self
    {
        if (!isset($this->data['metadata'])) {
            $this->data['metadata'] = [];
        }
        $this->data['metadata'][$key] = $value;
        return $this;
    }

    /**
     * Set redirect URL
     */
    public function redirectUrl(string $url): self
    {
        $this->data['redirect'] = ['url' => $url];
        return $this;
    }

    /**
     * Set post (webhook) URL
     */
    public function postUrl(string $url): self
    {
        $this->data['post'] = ['url' => $url];
        return $this;
    }

    /**
     * Get the built data array
     */
    public function toArray(): array
    {
        return $this->data;
    }

    /**
     * Build and execute the request
     */
    abstract public function create();
}
