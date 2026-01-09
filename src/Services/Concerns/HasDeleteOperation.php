<?php

declare(strict_types=1);

namespace TapPay\Tap\Services\Concerns;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

trait HasDeleteOperation
{
    /**
     * Delete a resource
     *
     * @param  string  $id  Resource ID
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If resource ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function delete(string $id): void
    {
        $this->client->delete(sprintf('%s/%s', $this->getEndpoint(), $id));
    }
}
