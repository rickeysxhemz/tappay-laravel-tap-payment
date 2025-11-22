<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Customer;

class CustomerService extends AbstractService
{
    /**
     * Get the endpoint for customers
     */
    protected function getEndpoint(): string
    {
        return 'customers';
    }

    /**
     * Create a new customer
     */
    public function create(array $data): Customer
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Customer($response);
    }

    /**
     * Retrieve a customer by ID
     */
    public function retrieve(string $customerId): Customer
    {
        $response = $this->client->get($this->getEndpoint() . '/' . $customerId);

        return new Customer($response);
    }

    /**
     * Update a customer
     */
    public function update(string $customerId, array $data): Customer
    {
        $response = $this->client->put($this->getEndpoint() . '/' . $customerId, $data);

        return new Customer($response);
    }

    /**
     * List all customers
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post($this->getEndpoint() . '/list', $params);

        return array_map(
            fn($customer) => new Customer($customer),
            $response['customers'] ?? []
        );
    }

    /**
     * Delete a customer
     */
    public function delete(string $customerId): bool
    {
        $this->client->delete($this->getEndpoint() . '/' . $customerId);

        return true;
    }
}
