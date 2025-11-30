<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Payout;
use TapPay\Tap\Services\Concerns\HasReadOperations;

/**
 * Service for tracking merchant settlements/payouts
 *
 * @extends AbstractService<Payout>
 */
class PayoutService extends AbstractService
{
    use HasReadOperations;

    protected function getEndpoint(): string
    {
        return 'payouts';
    }

    protected function getListKey(): string
    {
        return 'payouts';
    }

    protected function getResourceClass(): string
    {
        return Payout::class;
    }

    /**
     * List payouts for a specific merchant
     *
     * @param  string  $merchantId  Merchant ID
     * @param  array<string, mixed>  $params  Additional query parameters
     * @return array<Payout>
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function listByMerchant(string $merchantId, array $params = []): array
    {
        $params['merchant'] = $merchantId;

        /** @var array<Payout> */
        return $this->list($params);
    }

    /**
     * Download payout report
     *
     * @param  array<string, mixed>  $params  Report parameters including:
     *                                        - merchant: string (merchant ID)
     *                                        - period: array with start and end dates
     *                                        - format: string (csv, xlsx)
     * @return array<string, mixed> Report data or download URL
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function download(array $params = []): array
    {
        /** @var array<string, mixed> */
        return $this->client->post(sprintf('%s/download', $this->getEndpoint()), $params);
    }
}
