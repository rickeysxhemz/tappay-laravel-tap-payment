<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Payout;

/**
 * Service for tracking merchant settlements/payouts
 *
 * @extends AbstractService<Payout>
 */
class PayoutService extends AbstractService
{
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
     * Retrieve a payout by ID
     *
     * @param  string  $payoutId  Payout ID
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If payout ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function retrieve(string $payoutId): Payout
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $payoutId));

        return new Payout($response);
    }

    /**
     * List all payouts
     *
     * @param  array  $params  Query parameters including:
     *                         - limit: int (max results)
     *                         - starting_after: string (pagination cursor)
     *                         - merchant: string (filter by merchant ID)
     *                         - status: string (filter by status: PENDING, IN_PROGRESS, PAID, FAILED)
     *                         - arrival_date: array with gte/lte for date range
     * @return Payout[]
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function list(array $params = []): array
    {
        $response = $this->client->post(sprintf('%s/list', $this->getEndpoint()), $params);

        return $this->mapToResources($response);
    }

    /**
     * List payouts for a specific merchant
     *
     * @param  string  $merchantId  Merchant ID
     * @param  array  $params  Additional query parameters
     * @return Payout[]
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
     * Download payout report
     *
     * @param  array  $params  Report parameters including:
     *                         - merchant: string (merchant ID)
     *                         - period: array with start and end dates
     *                         - format: string (csv, xlsx)
     * @return array Report data or download URL
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If query parameters are invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function download(array $params = []): array
    {
        return $this->client->post(sprintf('%s/download', $this->getEndpoint()), $params);
    }
}
