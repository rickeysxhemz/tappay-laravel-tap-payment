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
        return match ($this) {
            self::CAPTURED, self::AUTHORIZED => true,
            default => false,
        };
    }

    /**
     * Check if the charge is pending
     */
    public function isPending(): bool
    {
        return match ($this) {
            self::INITIATED, self::IN_PROGRESS => true,
            default => false,
        };
    }

    /**
     * Check if the charge has failed
     */
    public function hasFailed(): bool
    {
        return match ($this) {
            self::FAILED,
            self::DECLINED,
            self::CANCELLED,
            self::ABANDONED,
            self::RESTRICTED,
            self::TIMEDOUT => true,
            default => false,
        };
    }

    /**
     * Get human-readable status label
     */
    public function label(): string
    {
        return match ($this) {
            self::INITIATED => 'Initiated',
            self::IN_PROGRESS => 'In Progress',
            self::ABANDONED => 'Abandoned',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
            self::DECLINED => 'Declined',
            self::RESTRICTED => 'Restricted',
            self::CAPTURED => 'Captured',
            self::VOID => 'Void',
            self::TIMEDOUT => 'Timed Out',
            self::UNKNOWN => 'Unknown',
            self::AUTHORIZED => 'Authorized',
        };
    }
}
