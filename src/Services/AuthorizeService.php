<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Authorize;

class AuthorizeService extends AbstractService
{
    /**
     * Get the endpoint for authorizations
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'authorize';
    }

    /**
     * Create a new authorization
     *
     * @param array $data Authorization data
     * @return Authorize
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
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return array_map(
            fn($auth) => new Authorize($auth),
            $response['authorizations'] ?? []
        );
    }
}
