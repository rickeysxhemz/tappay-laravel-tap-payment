<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Token;

class TokenService extends AbstractService
{
    /**
     * Get the endpoint for tokens
     */
    protected function getEndpoint(): string
    {
        return 'tokens';
    }

    /**
     * Create a new token for a saved card
     */
    public function create(array $data): Token
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Token($response);
    }

    /**
     * Retrieve a token by ID
     */
    public function retrieve(string $tokenId): Token
    {
        $response = $this->client->get($this->getEndpoint() . '/' . $tokenId);

        return new Token($response);
    }
}
