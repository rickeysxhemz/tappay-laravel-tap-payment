<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Controllers;

use const JSON_THROW_ON_ERROR;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Event;
use JsonException;
use TapPay\Tap\Enums\HttpStatus;
use TapPay\Tap\Events\WebhookProcessingFailed;
use TapPay\Tap\Events\WebhookReceived;
use TapPay\Tap\Events\WebhookValidationFailed;
use TapPay\Tap\Webhooks\WebhookValidationResult;
use TapPay\Tap\Webhooks\WebhookValidator;

use function config;
use function in_array;
use function is_array;
use function json_decode;
use function response;

/**
 * Handles incoming webhooks from Tap Payments
 */
class WebhookController extends Controller
{
    public function __construct(
        protected WebhookValidator $validator
    ) {}

    public function __invoke(Request $request): Response
    {
        $payload = $this->decodePayload($request);

        if ($payload instanceof Response) {
            return $payload;
        }

        $signatureResult = $this->validator->validatePayload(
            $payload,
            $request->header('x-tap-signature') ?? ''
        );

        if (! $signatureResult->isValid()) {
            return $this->failureResponse($request, $signatureResult, 'invalid_signature');
        }

        $toleranceResult = $this->validator->checkTolerance($payload);

        if (! $toleranceResult->isValid()) {
            return $this->failureResponse($request, $toleranceResult, 'expired');
        }

        $resourceValue = $payload['object'] ?? 'unknown';
        $resource = is_string($resourceValue) ? $resourceValue : 'unknown';
        $ip = $request->ip() ?? 'unknown';

        WebhookReceived::dispatch($resource, $payload, $ip);

        $this->dispatchResourceEvent($resource, $payload);

        return $this->successResponse();
    }

    /**
     * @return array<string, mixed>|Response
     */
    protected function decodePayload(Request $request): array|Response
    {
        try {
            $payload = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        } catch (JsonException $e) {
            return $this->invalidPayloadResponse($request, $e->getMessage());
        }

        if (! is_array($payload)) {
            return $this->invalidPayloadResponse($request, 'Payload is not an array');
        }

        /** @var array<string, mixed> */
        return $payload;
    }

    protected function invalidPayloadResponse(Request $request, string $error): Response
    {
        WebhookValidationFailed::dispatch('Invalid JSON payload', $request->ip() ?? 'unknown', ['json_error' => $error]);

        $message = config('tap.webhook.messages.invalid_payload', 'Invalid JSON payload');

        return response(is_string($message) ? $message : 'Invalid JSON payload', HttpStatus::BAD_REQUEST->value);
    }

    protected function failureResponse(
        Request $request,
        WebhookValidationResult $result,
        string $messageKey
    ): Response {
        WebhookValidationFailed::dispatch(
            $result->getError() ?? 'Validation failed',
            $request->ip() ?? 'unknown',
            $result->getContext()
        );

        $message = config("tap.webhook.messages.{$messageKey}", 'Validation failed');

        return response(is_string($message) ? $message : 'Validation failed', HttpStatus::BAD_REQUEST->value);
    }

    protected function successResponse(): Response
    {
        $message = config('tap.webhook.messages.success', 'Webhook received');

        return response(is_string($message) ? $message : 'Webhook received', HttpStatus::OK->value);
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
                Event::dispatch("tap.webhook.{$resource}", [$payload]);
            }

            Event::dispatch('tap.webhook.received', [$resource, $payload]);
        } catch (Exception $e) {
            WebhookProcessingFailed::dispatch($e, $resource, $payload);
        }
    }
}
