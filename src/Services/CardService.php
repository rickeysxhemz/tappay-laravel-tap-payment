<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Card;

class CardService extends AbstractService
{
    protected function getEndpoint(): string
    {
        return 'card';
    }

    protected function getListKey(): string
    {
        return 'cards';
    }

    protected function getResourceClass(): string
    {
        return Card::class;
    }

    /**
     * Retrieve a saved card
     *
     * @param string $customerId Customer ID
     * @param string $cardId Card ID
     * @return Card
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function retrieve(string $customerId, string $cardId): Card
    {
        $response = $this->client->get(sprintf('%s/%s/%s', $this->getEndpoint(), $customerId, $cardId));

        return new Card($response);
    }

    /**
     * List all cards for a customer
     *
     * @param string $customerId Customer ID
     * @param array $params Query parameters
     * @return Card[]
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function list(string $customerId, array $params = []): array
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $customerId), $params);

        return $this->mapToResources($response);
    }

    /**
     * Delete a saved card
     *
     * @param string $customerId Customer ID
     * @param string $cardId Card ID
     * @return void
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function delete(string $customerId, string $cardId): void
    {
        $this->client->delete(sprintf('%s/%s/%s', $this->getEndpoint(), $customerId, $cardId));
    }

    /**
     * Verify a card
     *
     * @param array $data Verification data
     * @return Card
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function verify(array $data): Card
    {
        $response = $this->client->post(sprintf('%s/verify', $this->getEndpoint()), $data);

        return new Card($response);
    }
}