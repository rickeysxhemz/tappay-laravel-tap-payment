<?php

declare(strict_types=1);

namespace TapPay\Tap\Resources;

use Carbon\Carbon;
use TapPay\Tap\Enums\InvoiceStatus;
use TapPay\Tap\Resources\Concerns\HasCustomer;
use TapPay\Tap\Resources\Concerns\HasMoney;
use TapPay\Tap\Resources\Concerns\HasPaymentStatus;

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
        $status = strtoupper($this->attributes['status'] ?? 'FAILED');

        return InvoiceStatus::tryFrom($status) ?? InvoiceStatus::FAILED;
    }

    public function description(): ?string
    {
        return $this->attributes['description'] ?? null;
    }

    public function url(): ?string
    {
        return $this->attributes['url'] ?? $this->attributes['invoice_url'] ?? null;
    }

    public function expiresAt(): ?Carbon
    {
        $expiry = $this->attributes['expiry'] ?? $this->attributes['expires_at'] ?? null;

        return $expiry ? $this->parseDateTime($expiry) : null;
    }

    public function paidAt(): ?Carbon
    {
        $paid = $this->attributes['paid_at'] ?? null;

        return $paid ? $this->parseDateTime($paid) : null;
    }

    public function chargeId(): ?string
    {
        return $this->attributes['charge_id'] ?? $this->get('charge.id');
    }

    public function isExpired(): bool
    {
        return $this->status() === InvoiceStatus::EXPIRED;
    }
}
