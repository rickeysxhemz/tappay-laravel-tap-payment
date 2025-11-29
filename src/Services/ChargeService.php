<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Builders\ChargeBuilder;
use TapPay\Tap\Contracts\MoneyContract;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Resources\Charge;

class ChargeService extends AbstractService
{
    public function __construct(
        Client $client,
        protected MoneyContract $money
    ) {
        parent::__construct($client);
    }

    protected function getEndpoint(): string
    {
        return 'charges';
    }

    protected function getListKey(): string
    {
        return 'charges';
    }

    protected function getResourceClass(): string
    {
        return Charge::class;
    }

    /**
     * Create a new charge builder
     */
    public function newBuilder(): ChargeBuilder
    {
        return new ChargeBuilder($this, $this->money);
    }

    /**
     * Create a new charge
     *
     * @param array $data Charge data
     * @return Charge
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function create(array $data): Charge
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Charge($response);
    }

    /**
     * Retrieve a charge by ID
     *
     * @param string $chargeId Charge ID
     * @return Charge
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If charge ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function retrieve(string $chargeId): Charge
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $chargeId));

        return new Charge($response);
    }

    /**
     * Update a charge
     *
     * @param string $chargeId Charge ID
     * @param array $data Update data
     * @return Charge
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If request parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function update(string $chargeId, array $data): Charge
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $chargeId), $data);

        return new Charge($response);
    }

    /**
     * List all charges
     *
     * @param array $params Query parameters
     * @return Charge[]
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