<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum RefundStatus: string
{
    case INITIATED = 'INITIATED';
    case PENDING = 'PENDING';
    case SUCCEEDED = 'SUCCEEDED';
    case FAILED = 'FAILED';
    case CANCELLED = 'CANCELLED';

    /**
     * Check if the refund is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::SUCCEEDED;
    }

    /**
     * Check if the refund is pending
     */
    public function isPending(): bool
    {
        return $this === self::INITIATED || $this === self::PENDING;
    }

    /**
     * Check if the refund has failed
     */
    public function hasFailed(): bool
    {
        return $this === self::FAILED || $this === self::CANCELLED;
    }
}
