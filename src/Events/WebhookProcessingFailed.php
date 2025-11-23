<?php

declare(strict_types=1);

namespace TapPay\Tap\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class WebhookProcessingFailed
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * Create a new event instance
     *
     * @param \Exception $exception The exception that was thrown
     * @param string $resource The webhook resource type
     * @param array $payload The webhook payload
     */
    public function __construct(
        public \Exception $exception,
        public string $resource,
        public array $payload
    ) {}

    /**
     * Get the webhook ID
     *
     * @return string|null
     */
    public function getId(): ?string
    {
        return $this->payload['id'] ?? null;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMessage(): string
    {
        return $this->exception->getMessage();
    }
}