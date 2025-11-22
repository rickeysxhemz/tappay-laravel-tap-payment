<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Authorize;

class AuthorizeService extends AbstractService
{
    /**
     * Get the endpoint for authorizations
     */
    protected function getEndpoint(): string
    {
        return 'authorize';
    }

    /**
     * Create a new authorization
     */
    public function create(array $data): Authorize
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Authorize($response);
    }

    /**
     * Retrieve an authorization by ID
     */
    public function retrieve(string $authId): Authorize
    {
        $response = $this->client->get($this->getEndpoint() . '/' . $authId);

        return new Authorize($response);
    }

    /**
     * Update an authorization
     */
    public function update(string $authId, array $data): Authorize
    {
        $response = $this->client->put($this->getEndpoint() . '/' . $authId, $data);

        return new Authorize($response);
    }

    /**
     * List all authorizations
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post($this->getEndpoint() . '/list', $params);

        return array_map(
            fn($auth) => new Authorize($auth),
            $response['authorizations'] ?? []
        );
    }
}
