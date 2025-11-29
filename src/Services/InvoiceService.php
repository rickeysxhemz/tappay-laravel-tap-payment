<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Invoice;

/**
 * @extends AbstractService<Invoice>
 */
class InvoiceService extends AbstractService
{
    protected function getEndpoint(): string
    {
        return 'invoices';
    }

    protected function getListKey(): string
    {
        return 'invoices';
    }

    protected function getResourceClass(): string
    {
        return Invoice::class;
    }

    /**
     * Create a new invoice
     *
     * @param  array  $data  Invoice data
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function create(array $data): Invoice
    {
        $response = $this->client->post($this->getEndpoint(), $data);

        return new Invoice($response);
    }

    /**
     * Retrieve an invoice by ID
     *
     * @param  string  $invoiceId  Invoice ID
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function retrieve(string $invoiceId): Invoice
    {
        $response = $this->client->get(sprintf('%s/%s', $this->getEndpoint(), $invoiceId));

        return new Invoice($response);
    }

    /**
     * Update an invoice
     *
     * @param  string  $invoiceId  Invoice ID
     * @param  array  $data  Update data
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function update(string $invoiceId, array $data): Invoice
    {
        $response = $this->client->put(sprintf('%s/%s', $this->getEndpoint(), $invoiceId), $data);

        return new Invoice($response);
    }

    /**
     * Cancel/finalize an invoice
     *
     * @param  string  $invoiceId  Invoice ID
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function finalize(string $invoiceId): Invoice
    {
        $response = $this->client->post(sprintf('%s/%s/finalize', $this->getEndpoint(), $invoiceId), []);

        return new Invoice($response);
    }

    /**
     * List all invoices
     *
     * @param  array  $params  Query parameters
     * @return Invoice[]
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
