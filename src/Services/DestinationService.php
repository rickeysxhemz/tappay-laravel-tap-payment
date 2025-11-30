<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Destination;
use TapPay\Tap\Services\Concerns\HasReadOperations;

/**
 * Service for managing payment split destinations
 *
 * @extends AbstractService<Destination>
 */
class DestinationService extends AbstractService
{
    use HasReadOperations;

    protected function getEndpoint(): string
    {
        return 'destinations';
    }

    protected function getListKey(): string
    {
        return 'destinations';
    }

    protected function getResourceClass(): string
    {
        return Destination::class;
    }

    /**
     * List destinations for a specific merchant
     *
     * @param  string  $merchantId  Merchant ID
     * @param  array  $params  Additional query parameters
     * @return Destination[]
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function listByMerchant(string $merchantId, array $params = []): array
    {
        $params['merchant'] = $merchantId;

        return $this->list($params);
    }

    /**
     * List destinations for a specific charge
     *
     * @param  string  $chargeId  Charge ID
     * @param  array  $params  Additional query parameters
     * @return Destination[]
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function listByCharge(string $chargeId, array $params = []): array
    {
        $params['charge'] = $chargeId;

        return $this->list($params);
    }
}
