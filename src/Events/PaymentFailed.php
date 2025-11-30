<?php

declare(strict_types=1);

namespace TapPay\Tap\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use TapPay\Tap\Resources\Charge;

class PaymentFailed
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

    public function getStatus(): string
    {
        return $this->charge->status()->value;
    }

    public function getResponseCode(): ?string
    {
        $code = $this->charge->get('response.code');

        return is_string($code) ? $code : null;
    }

    public function getResponseMessage(): ?string
    {
        $message = $this->charge->get('response.message');

        return is_string($message) ? $message : null;
    }
}
