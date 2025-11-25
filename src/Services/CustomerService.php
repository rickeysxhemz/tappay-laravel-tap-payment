<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Customer;

use function array_map;
use function sprintf;

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
     *
     * @param array $data Customer data
     * @return Customer
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
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
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If customer ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
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
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
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
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
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
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If customer ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function delete(string $customerId): bool
    {
        $this->client->delete(sprintf('%s/%s', $this->getEndpoint(), $customerId));

        return true;
    }
}