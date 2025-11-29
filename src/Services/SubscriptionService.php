<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Subscription;

/**
 * @extends AbstractService<Subscription>
 */
class SubscriptionService extends AbstractService
{
    protected function getEndpoint(): string
    {
        return 'subscription';
    }

    protected function getListKey(): string
    {
        return 'subscriptions';
    }

    protected function getResourceClass(): string
    {
        return Subscription::class;
    }

    /**
     * Create a new subscription
     *
     * @param  array  $data  Subscription data including:
     *                       - amount: float
     *                       - currency: string
     *                       - customer: array with id
     *                       - term: array with interval (DAILY|WEEKLY|MONTHLY|YEARLY), period (int)
     *                       - trial: array with days (int) - optional
     *                       - source: array with id (card token or payment method)
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function create(array $data): Subscription
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Subscription($response);
    }

    /**
     * Retrieve a subscription by ID
     *
     * @param  string  $subscriptionId  Subscription ID
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function retrieve(string $subscriptionId): Subscription
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $subscriptionId));

        return new Subscription($response);
    }

    /**
     * Update a subscription
     *
     * @param  string  $subscriptionId  Subscription ID
     * @param  array  $data  Update data
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function update(string $subscriptionId, array $data): Subscription
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $subscriptionId), $data);

        return new Subscription($response);
    }

    /**
     * Cancel a subscription
     *
     * @param  string  $subscriptionId  Subscription ID
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function cancel(string $subscriptionId): Subscription
    {
        $response = $this->client->delete(sprintf('%s/%s', $this->getEndpoint(), $subscriptionId));

        return new Subscription($response);
    }

    /**
     * List all subscriptions
     *
     * @param  array  $params  Query parameters
     * @return Subscription[]
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return $this->mapToResources($response);
    }
}
