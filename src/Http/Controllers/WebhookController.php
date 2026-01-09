<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Controllers;

use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use TapPay\Tap\Http\Handlers\WebhookHandler;
use TapPay\Tap\Http\Requests\WebhookRequest;

class WebhookController extends Controller
{
    public function __construct(
        protected WebhookHandler $handler
    ) {}

    public function __invoke(WebhookRequest $request): Response
    {
        $this->handler->handle(
            $request->validated(),
            $request->ip() ?? 'unknown'
        );

        return new Response('Webhook received', 200);
    }
}
