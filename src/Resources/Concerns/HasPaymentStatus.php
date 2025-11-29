<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources\Concerns;

/**
 * Trait for resources that have payment status helpers
 *
 * Requires the class to implement a status() method that returns an enum
 * with isSuccessful(), isPending(), and hasFailed() methods.
 */
trait HasPaymentStatus
{
    /**
     * Check if the payment was successful
     */
    public function isSuccessful(): bool
    {
        return $this->status()->isSuccessful();
    }

    /**
     * Check if the payment is pending
     */
    public function isPending(): bool
    {
        return $this->status()->isPending();
    }

    /**
     * Check if the payment has failed
     */
    public function hasFailed(): bool
    {
        return $this->status()->hasFailed();
    }
}
