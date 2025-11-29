<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

/**
 * Trait for resources that have transaction details (redirect URL, source)
 */
trait HasTransaction
{
    /**
     * Get the transaction URL for redirect
     */
    public function transactionUrl(): ?string
    {
        return $this->get('transaction.url');
    }

    /**
     * Get the source ID
     */
    public function sourceId(): ?string
    {
        return $this->get('source.id');
    }
}
