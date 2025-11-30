<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Subscription;
use TapPay\Tap\Services\Concerns\HasStandardOperations;

/**
 * @extends AbstractService<Subscription>
 */
class SubscriptionService extends AbstractService
{
    use HasStandardOperations;

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
     * Cancel a subscription
     *
     * @param  string  $subscriptionId  Subscription ID
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If subscription ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function cancel(string $subscriptionId): Subscription
    {
        $response = $this->client->delete(sprintf('%s/%s', $this->getEndpoint(), $subscriptionId));

        return new Subscription($response);
    }
}
