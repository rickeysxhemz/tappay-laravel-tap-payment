<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\RefundStatus;
use TapPay\Tap\Exceptions\InvalidStatusException;
use TapPay\Tap\Resources\Concerns\HasMoney;
use TapPay\Tap\Resources\Concerns\HasPaymentStatus;

class Refund extends Resource
{
    use HasMoney;
    use HasPaymentStatus;

    protected function getIdPrefix(): string
    {
        return 'ref_';
    }

    /**
     * Get the refund status
     *
     * @throws InvalidStatusException
     */
    public function status(): RefundStatus
    {
        $status = $this->attributes['status'] ?? null;

        if ($status === null) {
            return RefundStatus::FAILED;
        }

        return RefundStatus::tryFrom(strtoupper($status))
            ?? InvalidStatusException::unknown($status, 'refund');
    }

    /**
     * Get the charge ID being refunded
     */
    public function chargeId(): string
    {
        return $this->attributes['charge_id'] ?? '';
    }

    /**
     * Get the refund reason
     */
    public function reason(): ?string
    {
        return $this->attributes['reason'] ?? null;
    }
}
