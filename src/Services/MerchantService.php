<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Merchant;

/**
 * Service for managing marketplace sub-merchants
 *
 * @extends AbstractService<Merchant>
 */
class MerchantService extends AbstractService
{
    protected function getEndpoint(): string
    {
        return 'merchants';
    }

    protected function getListKey(): string
    {
        return 'merchants';
    }

    protected function getResourceClass(): string
    {
        return Merchant::class;
    }

    /**
     * Create a new sub-merchant
     *
     * @param  array  $data  Merchant data including:
     *                       - name: string (required)
     *                       - email: string (required)
     *                       - phone: array with country_code and number
     *                       - country_code: string (e.g., 'SA', 'KW')
     *                       - type: string ('individual' or 'company')
     *                       - business: array with business details
     *                       - bank_account: array with bank details
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function create(array $data): Merchant
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Merchant($response);
    }

    /**
     * Retrieve a merchant by ID
     *
     * @param  string  $merchantId  Merchant ID
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If merchant ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function retrieve(string $merchantId): Merchant
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $merchantId));

        return new Merchant($response);
    }

    /**
     * Update a merchant
     *
     * @param  string  $merchantId  Merchant ID
     * @param  array  $data  Update data
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function update(string $merchantId, array $data): Merchant
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $merchantId), $data);

        return new Merchant($response);
    }

    /**
     * Archive/delete a merchant
     *
     * @param  string  $merchantId  Merchant ID
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If merchant ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function delete(string $merchantId): void
    {
        $this->client->delete(sprintf('%s/%s', $this->getEndpoint(), $merchantId));
    }

    /**
     * List all merchants
     *
     * @param  array  $params  Query parameters including:
     *                         - limit: int (max results)
     *                         - starting_after: string (pagination cursor)
     *                         - status: string (filter by status)
     * @return Merchant[]
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
}
