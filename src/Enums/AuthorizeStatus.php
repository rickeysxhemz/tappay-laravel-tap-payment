<?php

declare(strict_types=1);

namespace TapPay\Tap\Enums;

enum AuthorizeStatus: string
{
    case INITIATED = 'INITIATED';
    case AUTHORIZED = 'AUTHORIZED';
    case CAPTURED = 'CAPTURED';
    case CANCELLED = 'CANCELLED';
    case FAILED = 'FAILED';
    case DECLINED = 'DECLINED';
    case RESTRICTED = 'RESTRICTED';
    case VOID = 'VOID';
    case UNKNOWN = 'UNKNOWN';

    /**
     * Check if the authorization is successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return match($this) {
            self::AUTHORIZED => true,
            default => false,
        };
    }

    /**
     * Check if the authorization is pending
     *
     * @return bool
     */
    public function isPending(): bool
    {
        return match($this) {
            self::INITIATED => true,
            default => false,
        };
    }

    /**
     * Check if the authorization has failed
     *
     * @return bool
     */
    public function hasFailed(): bool
    {
        return match($this) {
            self::FAILED,
            self::DECLINED,
            self::CANCELLED,
            self::RESTRICTED,
            self::VOID => true,
            default => false,
        };
    }

    /**
     * Get human-readable status label
     *
     * @return string
     */
    public function label(): string
    {
        return match($this) {
            self::INITIATED => 'Initiated',
            self::AUTHORIZED => 'Authorized',
            self::CAPTURED => 'Captured',
            self::CANCELLED => 'Cancelled',
            self::FAILED => 'Failed',
            self::DECLINED => 'Declined',
            self::RESTRICTED => 'Restricted',
            self::VOID => 'Void',
            self::UNKNOWN => 'Unknown',
        };
    }
}