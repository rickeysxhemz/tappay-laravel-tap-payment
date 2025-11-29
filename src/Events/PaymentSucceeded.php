<?php

declare(strict_types=1);

namespace TapPay\Tap\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use TapPay\Tap\Resources\Charge;

class PaymentSucceeded
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public function __construct(
        public Charge $charge,
        public ?string $redirectUrl = null
    ) {}

    public function getChargeId(): string
    {
        return $this->charge->id();
    }

    public function getAmount(): float
    {
        return $this->charge->amount();
    }

    public function getCurrency(): string
    {
        return $this->charge->currency();
    }

    public function getCustomerId(): ?string
    {
        return $this->charge->customerId();
    }
}
