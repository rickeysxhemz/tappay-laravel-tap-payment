<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use TapPay\Tap\Events\WebhookProcessingFailed;
use TapPay\Tap\Events\WebhookReceived;

class WebhookController extends Controller
{
    /**
     * Create a new webhook controller instance
     *
     * @param WebhookValidator $validator Webhook signature validator
     */
    public function __construct(
        protected WebhookValidator $validator
    ) {}

    /**
     * Handle incoming webhook from Tap Payments
     *
     * @param Request $request The webhook request
     * @return Response HTTP response (200 on success, 400 on failure)
     */
    public function __invoke(Request $request): Response
    {
        // Decode payload once
        $payload = json_decode($request->getContent(), true);

        // Validate webhook signature using already-decoded payload
        if (!$this->validator->validatePayload($payload ?? [], $request->header('x-tap-signature') ?? '')) {
            return response(
                config('tap.webhook_messages.invalid_signature', 'Invalid signature'),
                400
            );
        }

        // Check tolerance (prevents replay attacks)
        if (!$this->validator->isWithinTolerance($payload)) {
            return response(
                config('tap.webhook_messages.expired', 'Webhook expired'),
                400
            );
        }

        $resource = $payload['object'] ?? 'unknown';

        // Dispatch WebhookReceived event
        WebhookReceived::dispatch(
            $resource,
            $payload,
            $request->ip()
        );

        // Dispatch event based on resource type
        $this->dispatchWebhookEvent($payload);

        return response(
            config('tap.webhook_messages.success', 'Webhook received'),
            200
        );
    }

    /**
     * Dispatch webhook events to Laravel event system
     *
     * @param array $payload The webhook payload
     * @return void
     */
    protected function dispatchWebhookEvent(array $payload): void
    {
        $resource = $payload['object'] ?? 'unknown';

        // Validate resource type to prevent arbitrary event names
        $allowedResources = config('tap.webhook_allowed_resources', [
            'charge', 'refund', 'customer', 'authorize', 'token'
        ]);

        try {
            // Dispatch resource-specific event only if allowed
            if (in_array($resource, $allowedResources, true)) {
                Event::dispatch("tap.webhook.{$resource}", [$payload]);
            }

            // Always dispatch general webhook event
            Event::dispatch('tap.webhook.received', [$resource, $payload]);
        } catch (\Exception $e) {
            // Dispatch processing failed event instead of logging
            WebhookProcessingFailed::dispatch(
                $e,
                $resource,
                $payload
            );
            // Don't throw - we still return 200 to prevent webhook retries
        }
    }
}
