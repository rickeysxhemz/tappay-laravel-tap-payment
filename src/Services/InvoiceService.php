<?php

declare(strict_types=1);

namespace TapPay\Tap\Services;

use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Resources\Invoice;
use TapPay\Tap\Services\Concerns\HasCrudOperations;

/**
 * @extends AbstractService<Invoice>
 */
class InvoiceService extends AbstractService
{
    use HasCrudOperations;

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
     * Finalize an invoice
     *
     * @param  string  $invoiceId  Invoice ID
     *
     * @throws AuthenticationException If API authentication fails
     * @throws InvalidRequestException If invoice ID is invalid
     * @throws ApiErrorException If API returns an error or network error occurs
     */
    public function finalize(string $invoiceId): Invoice
    {
        /** @var array<string, mixed> $response */
        $response = $this->client->post(sprintf('%s/%s/finalize', $this->getEndpoint(), $invoiceId), []);

        return new Invoice($response);
    }
}
