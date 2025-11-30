<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use TapPay\Tap\Enums\ChargeStatus;
use TapPay\Tap\Exceptions\InvalidStatusException;
use TapPay\Tap\Resources\Concerns\HasPaymentDetails;

use function is_string;

class Charge extends Resource
{
    use HasPaymentDetails;

    protected function getIdPrefix(): string
    {
        return 'chg_';
    }

    /**
     * Get the charge status
     *
     * @throws InvalidStatusException
     */
    public function status(): ChargeStatus
    {
        $status = $this->attributes['status'] ?? null;

        if (! is_string($status)) {
            return ChargeStatus::UNKNOWN;
        }

        return ChargeStatus::tryFrom(strtoupper($status))
            ?? InvalidStatusException::unknown($status, 'charge');
    }

    /**
     * Get the description
     */
    public function description(): ?string
    {
        return $this->getNullableString('description');
    }

    /**
     * Get saved card ID if card was saved
     */
    public function cardId(): ?string
    {
        $cardId = $this->get('card.id');

        return is_string($cardId) ? $cardId : null;
    }
}
