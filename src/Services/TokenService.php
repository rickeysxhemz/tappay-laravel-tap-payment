<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Token;

/**
 * @extends AbstractService<Token>
 */
class TokenService extends AbstractService
{
    /**
     * Get the endpoint for tokens
     */
    protected function getEndpoint(): string
    {
        return 'tokens';
    }

    protected function getListKey(): string
    {
        return 'tokens';
    }

    protected function getResourceClass(): string
    {
        return Token::class;
    }

    /**
     * Create a new token for a saved card
     *
     * @param  array  $data  Token data
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function create(array $data): Token
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Token($response);
    }

    /**
     * Retrieve a token by ID
     *
     * @param  string  $tokenId  Token ID
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If token ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function retrieve(string $tokenId): Token
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $tokenId));

        return new Token($response);
    }
}
