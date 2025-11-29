<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Authorize;

class AuthorizeService extends AbstractService
{
    protected function getEndpoint(): string
    {
        return 'authorize';
    }

    protected function getListKey(): string
    {
        return 'authorizations';
    }

    protected function getResourceClass(): string
    {
        return Authorize::class;
    }

    /**
     * Create a new authorization
     *
     * @param array $data Authorization data
     * @return Authorize
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function create(array $data): Authorize
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Authorize($response);
    }

    /**
     * Retrieve an authorization by ID
     *
     * @param string $authId Authorization ID
     * @return Authorize
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If authorization ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function retrieve(string $authId): Authorize
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $authId));

        return new Authorize($response);
    }

    /**
     * Update an authorization
     *
     * @param string $authId Authorization ID
     * @param array $data Update data
     * @return Authorize
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function update(string $authId, array $data): Authorize
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $authId), $data);

        return new Authorize($response);
    }

    /**
     * List all authorizations
     *
     * @param array $params Query parameters
     * @return Authorize[]
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