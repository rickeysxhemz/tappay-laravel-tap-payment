<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Handlers;

use Exception;
use Illuminate\Support\Facades\Event;
use TapPay\Tap\Events\WebhookProcessingFailed;
use TapPay\Tap\Events\WebhookReceived;

use function config;
use function in_array;
use function is_string;
use function preg_replace;

class WebhookHandler
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function handle(array $payload, string $ip): void
    {
        $resourceValue = $payload['object'] ?? 'unknown';
        $resource = is_string($resourceValue) ? $resourceValue : 'unknown';
        $sanitizedResource = (string) preg_replace('/[^a-zA-Z0-9_]/', '', $resource);

        WebhookReceived::dispatch($resource, $payload, $ip);

        $this->dispatchResourceEvent($sanitizedResource, $payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    protected function dispatchResourceEvent(string $resource, array $payload): void
    {
        /** @var array<int, string> $allowedResources */
        $allowedResources = config('tap.webhook.allowed_resources');

        if (empty($allowedResources)) {
            return;
        }

        try {
            if (in_array($resource, $allowedResources, true)) {
                Event::dispatch('tap.webhook.' . $resource, [$payload]);
            }

            Event::dispatch('tap.webhook.received', [$resource, $payload]);
        } catch (Exception $e) {
            WebhookProcessingFailed::dispatch($e, $resource, $payload);
        }
    }
}
