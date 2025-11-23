<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Resources\Token;

class TokenService extends AbstractService
{
    /**
     * Get the endpoint for tokens
     *
     * @return string
     */
    protected function getEndpoint(): string
    {
        return 'tokens';
    }

    /**
     * Create a new token for a saved card
     *
     * @param array $data Token data
     * @return Token
     */
    public function create(array $data): Token
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Token($response);
    }

    /**
     * Retrieve a token by ID
     *
     * @param string $tokenId Token ID
     * @return Token
     */
    public function retrieve(string $tokenId): Token
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $tokenId));

        return new Token($response);
    }
}
