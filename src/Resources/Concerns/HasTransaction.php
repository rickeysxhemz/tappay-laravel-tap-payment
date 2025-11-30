<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

use function is_string;

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
        $url = $this->get('transaction.url');

        return is_string($url) ? $url : null;
    }

    /**
     * Get the source ID
     */
    public function sourceId(): ?string
    {
        $sourceId = $this->get('source.id');

        return is_string($sourceId) ? $sourceId : null;
    }
}
