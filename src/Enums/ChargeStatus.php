<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum ChargeStatus: string
{
    case INITIATED = 'INITIATED';
    case IN_PROGRESS = 'IN_PROGRESS';
    case ABANDONED = 'ABANDONED';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';
    case DECLINED = 'DECLINED';
    case RESTRICTED = 'RESTRICTED';
    case CAPTURED = 'CAPTURED';
    case VOID = 'VOID';
    case TIMEDOUT = 'TIMEDOUT';
    case UNKNOWN = 'UNKNOWN';
    case AUTHORIZED = 'AUTHORIZED';

    /**
     * Check if the charge is successful
     */
    public function isSuccessful(): bool
    {
        return $this === self::CAPTURED || $this === self::AUTHORIZED;
    }

    /**
     * Check if the charge is pending
     */
    public function isPending(): bool
    {
        return $this === self::INITIATED || $this === self::IN_PROGRESS;
    }

    /**
     * Check if the charge has failed
     */
    public function hasFailed(): bool
    {
        return in_array($this, [
            self::FAILED,
            self::DECLINED,
            self::CANCELLED,
            self::ABANDONED,
            self::RESTRICTED,
            self::TIMEDOUT,
        ]);
    }
}
