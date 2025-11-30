<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Card;

/**
 * @extends AbstractService<Card>
 */
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
     * @param  string  $customerId  Customer ID
     * @param  string  $cardId  Card ID
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function retrieve(string $customerId, string $cardId): Card
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->get(sprintf('%s/%s/%s', $this->getEndpoint(), $customerId, $cardId));

        return new Card($response);
    }

    /**
     * List all cards for a customer
     *
     * @param  string  $customerId  Customer ID
     * @param  array<string, mixed>  $params  Query parameters
     * @return array<Card>
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function list(string $customerId, array $params = []): array
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $customerId), $params);

        return $this->mapToResources($response);
    }

    /**
     * Delete a saved card
     *
     * @param  string  $customerId  Customer ID
     * @param  string  $cardId  Card ID
     *
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
     * @param  array<string, mixed>  $data  Verification data
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function verify(array $data): Card
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->post(sprintf('%s/verify', $this->getEndpoint()), $data);

        return new Card($response);
    }
}
