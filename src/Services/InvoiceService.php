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
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function finalize(string $invoiceId): Invoice
    {
        $this->validateInvoiceId($invoiceId);

        /** @var array<string, mixed> $response */
        $response = $this->client->post(sprintf('%s/%s/finalize', $this->getEndpoint(), $invoiceId), []);

        return new Invoice($response);
    }

    /**
     * Send a payment reminder for an invoice
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function remind(string $invoiceId): Invoice
    {
        $this->validateInvoiceId($invoiceId);

        /** @var array<string, mixed> $response */
        $response = $this->client->post(sprintf('%s/%s/remind', $this->getEndpoint(), $invoiceId), []);

        return new Invoice($response);
    }

    /**
     * Cancel an invoice
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    public function cancel(string $invoiceId): void
    {
        $this->validateInvoiceId($invoiceId);

        $this->client->delete(sprintf('%s/%s', $this->getEndpoint(), $invoiceId));
    }

    /**
     * @throws InvalidRequestException
     */
    private function validateInvoiceId(string $invoiceId): void
    {
        if (! str_starts_with($invoiceId, 'inv_')) {
            throw new InvalidRequestException('Invoice ID must start with "inv_"');
        }
    }
}
