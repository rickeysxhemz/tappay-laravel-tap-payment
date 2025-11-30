<?php

declare(strict_types=1);

namespace TapPay\Tap\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookReceived
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * Create a new event instance
     *
     * @param  string  $resource  The webhook resource type (charge, refund, etc.)
     * @param  array  $payload  The full webhook payload
     * @param  string  $ip  The IP address of the request
     */
    public function __construct(
        public string $resource,
        public array $payload,
        public string $ip
    ) {}

    /**
     * Get the webhook ID
     */
    public function getId(): ?string
    {
        $id = $this->payload['id'] ?? null;

        return is_string($id) ? $id : null;
    }

    /**
     * Check if this is a specific resource type
     *
     * @param  string  $type  The resource type to check
     */
    public function isType(string $type): bool
    {
        return $this->resource === $type;
    }
}
