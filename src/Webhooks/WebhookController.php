<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;

class WebhookController extends Controller
{
    protected WebhookValidator $validator;

    public function __construct(WebhookValidator $validator)
    {
        $this->validator = $validator;
    }

    /**
     * Handle incoming webhook
     */
    public function __invoke(Request $request): Response
    {
        // Validate webhook signature
        if (!$this->validator->validate($request)) {
            return response('Invalid signature', 400);
        }

        $payload = json_decode($request->getContent(), true);

        // Check tolerance
        if (!$this->validator->isWithinTolerance($payload)) {
            return response('Webhook expired', 400);
        }

        // Dispatch event based on resource type
        $this->dispatchWebhookEvent($payload);

        return response('Webhook received', 200);
    }

    /**
     * Dispatch webhook event
     */
    protected function dispatchWebhookEvent(array $payload): void
    {
        $resource = $payload['object'] ?? 'unknown';
        $id = $payload['id'] ?? null;

        // Dispatch Laravel event
        Event::dispatch("tap.webhook.{$resource}", [$payload]);

        // Also dispatch a general webhook event
        Event::dispatch('tap.webhook.received', [$resource, $payload]);
    }
}
