<?php

declare(strict_types=1);

namespace TapPay\Tap\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class WebhookValidationFailed
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance
     *
     * @param  string  $reason  The reason why validation failed
     * @param  string  $ip  The IP address of the request
     * @param  array  $context  Additional context about the failure
     */
    public function __construct(
        public string $reason,
        public string $ip,
        public array $context = []
    ) {}

    /**
     * Create from Request object
     *
     * @param  string  $reason  The failure reason
     * @param  Request  $request  The webhook request
     * @param  array  $context  Additional context
     */
    public static function fromRequest(string $reason, Request $request, array $context = []): self
    {
        return new self(
            reason: $reason,
            ip: $request->ip(),
            context: array_merge($context, [
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ])
        );
    }
}
