<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use Carbon\Carbon;
use TapPay\Tap\Enums\InvoiceStatus;
use TapPay\Tap\Resources\Concerns\HasCustomer;
use TapPay\Tap\Resources\Concerns\HasMoney;
use TapPay\Tap\Resources\Concerns\HasPaymentStatus;

use function is_int;
use function is_string;

class Invoice extends Resource
{
    use HasCustomer;
    use HasMoney;
    use HasPaymentStatus;

    protected function getIdPrefix(): string
    {
        return 'inv_';
    }

    public function status(): InvoiceStatus
    {
        $status = $this->getString('status', 'FAILED');

        return InvoiceStatus::tryFrom(strtoupper($status)) ?? InvoiceStatus::FAILED;
    }

    public function description(): ?string
    {
        return $this->getNullableString('description');
    }

    public function url(): ?string
    {
        $url = $this->attributes['url'] ?? $this->attributes['invoice_url'] ?? null;

        return is_string($url) ? $url : null;
    }

    public function expiresAt(): ?Carbon
    {
        $expiry = $this->attributes['expiry'] ?? $this->attributes['expires_at'] ?? null;

        if ($expiry === null) {
            return null;
        }

        return is_string($expiry) || is_int($expiry) ? $this->parseDateTime($expiry) : null;
    }

    public function paidAt(): ?Carbon
    {
        $paid = $this->attributes['paid_at'] ?? null;

        if ($paid === null) {
            return null;
        }

        return is_string($paid) || is_int($paid) ? $this->parseDateTime($paid) : null;
    }

    public function chargeId(): ?string
    {
        $chargeId = $this->attributes['charge_id'] ?? $this->get('charge.id');

        return is_string($chargeId) ? $chargeId : null;
    }

    public function isExpired(): bool
    {
        return $this->status() === InvoiceStatus::EXPIRED;
    }
}
