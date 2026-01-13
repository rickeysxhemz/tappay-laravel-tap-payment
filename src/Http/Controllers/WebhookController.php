<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use TapPay\Tap\Http\Handlers\WebhookProcessor;

use function config;
use function is_string;

class WebhookController extends Controller
{
    public function __construct(
        private WebhookProcessor $processor,
    ) {}

    public function __invoke(Request $request): Response
    {
        $result = $this->processor->process(
            content: $request->getContent(),
            signature: $request->header('hashstring', ''),
            ip: $request->ip() ?? 'unknown',
        );

        $statusCode = $result->isSuccess() ? 200 : 400;
        $message = $this->getMessage($result->code, $result->message);

        return new Response($message, $statusCode);
    }

    private function getMessage(string $code, string $fallback): string
    {
        $configMessage = config("tap.webhook.messages.{$code}", $fallback);

        return is_string($configMessage) ? $configMessage : $fallback;
    }
}
