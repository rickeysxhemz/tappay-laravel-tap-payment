<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Refund;

class RefundService extends AbstractService
{
    protected function getEndpoint(): string
    {
        return 'refunds';
    }

    protected function getListKey(): string
    {
        return 'refunds';
    }

    protected function getResourceClass(): string
    {
        return Refund::class;
    }

    /**
     * Create a new refund
     *
     * @param array $data Refund data
     * @return Refund
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function create(array $data): Refund
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Refund($response);
    }

    /**
     * Retrieve a refund by ID
     *
     * @param string $refundId Refund ID
     * @return Refund
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If refund ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function retrieve(string $refundId): Refund
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $refundId));

        return new Refund($response);
    }

    /**
     * Update a refund
     *
     * @param string $refundId Refund ID
     * @param array $data Update data
     * @return Refund
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function update(string $refundId, array $data): Refund
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $refundId), $data);

        return new Refund($response);
    }

    /**
     * List all refunds
     *
     * @param array $params Query parameters
     * @return Refund[]
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