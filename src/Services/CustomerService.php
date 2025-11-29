<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Customer;

/**
 * @extends AbstractService<Customer>
 */
class CustomerService extends AbstractService
{
    protected function getEndpoint(): string
    {
        return 'customers';
    }

    protected function getListKey(): string
    {
        return 'customers';
    }

    protected function getResourceClass(): string
    {
        return Customer::class;
    }

    /**
     * Create a new customer
     *
     * @param  array  $data  Customer data
     *
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
     * @param  string  $customerId  Customer ID
     *
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
     * @param  string  $customerId  Customer ID
     * @param  array  $data  Update data
     *
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
     * @param  array  $params  Query parameters
     * @return Customer[]
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return $this->mapToResources($response);
    }

    /**
     * Delete a customer
     *
     * @param  string  $customerId  Customer ID
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If customer ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function delete(string $customerId): void
    {
        $this->client->delete(sprintf('%s/%s', $this->getEndpoint(), $customerId));
    }
}
