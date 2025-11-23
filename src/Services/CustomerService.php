<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Customer;

class CustomerService extends AbstractService
{
    /**
     * Get the endpoint for customers
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'customers';
    }

    /**
     * Create a new customer
     *
     * @param array $data Customer data
     * @return Customer
     */
    public function create(array $data): Customer
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Customer($response);
    }

    /**
     * Retrieve a customer by ID
     *
     * @param string $customerId Customer ID
     * @return Customer
     */
    public function retrieve(string $customerId): Customer
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $customerId));

        return new Customer($response);
    }

    /**
     * Update a customer
     *
     * @param string $customerId Customer ID
     * @param array $data Update data
     * @return Customer
     */
    public function update(string $customerId, array $data): Customer
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $customerId), $data);

        return new Customer($response);
    }

    /**
     * List all customers
     *
     * @param array $params Query parameters
     * @return Customer[]
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return array_map(
            fn($customer) => new Customer($customer),
            $response['customers'] ?? []
        );
    }

    /**
     * Delete a customer
     *
     * @param string $customerId Customer ID
     * @return bool
     */
    public function delete(string $customerId): bool
    {
        $this->client->delete(sprintf('%s/%s', $this->getEndpoint(), $customerId));

        return true;
    }
}
