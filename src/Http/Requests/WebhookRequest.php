<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Response;
use TapPay\Tap\Contracts\WebhookSecretResolverInterface;
use TapPay\Tap\Events\WebhookValidationFailed;
use TapPay\Tap\Webhooks\WebhookValidator;

use function config;
use function is_string;

class WebhookRequest extends FormRequest
{
    /** @var array<string, mixed> */
    protected array $decodedPayload = [];

    protected ?WebhookValidator $cachedValidator = null;

    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'amount' => ['required', 'numeric'],
            'currency' => ['required', 'string'],
            'status' => ['required', 'string'],
            'created' => ['required', 'numeric'],
            'gateway' => ['sometimes', 'array'],
            'reference' => ['sometimes', 'array'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $content = $this->getContent();

        if ($content === '') {
            $this->failWithResponse('Empty payload', 'invalid_payload');
        }

        $decoded = json_decode($content, true, 64);

        if (json_last_error() !== JSON_ERROR_NONE || ! is_array($decoded)) {
            $this->failWithResponse('Invalid JSON payload', 'invalid_payload');
        }

        /** @var array<string, mixed> $decoded */
        $this->decodedPayload = $decoded;
        $this->merge($decoded);

        $this->validateSignature();
        $this->validateTolerance();
    }

    protected function validateSignature(): void
    {
        $validator = $this->resolveValidator();
        $signature = $this->header('hashstring') ?? '';

        $result = $validator->validatePayload($this->decodedPayload, $signature);

        if (! $result->isValid()) {
            $this->failWithResponse($result->getError() ?? 'Invalid signature', 'invalid_signature');
        }
    }

    protected function validateTolerance(): void
    {
        $validator = $this->resolveValidator();
        $result = $validator->checkTolerance($this->decodedPayload);

        if (! $result->isValid()) {
            $this->failWithResponse($result->getError() ?? 'Webhook expired', 'expired');
        }
    }

    protected function resolveValidator(): WebhookValidator
    {
        if ($this->cachedValidator !== null) {
            return $this->cachedValidator;
        }

        /** @var WebhookSecretResolverInterface $resolver */
        $resolver = app(WebhookSecretResolverInterface::class);
        $customSecret = $resolver->resolve($this->decodedPayload);

        if ($customSecret !== null && $customSecret !== '') {
            return $this->cachedValidator = new WebhookValidator($customSecret);
        }

        return $this->cachedValidator = app(WebhookValidator::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function validated($key = null, $default = null): array
    {
        return $this->decodedPayload;
    }

    /**
     * @return never
     */
    protected function failWithResponse(string $error, string $messageKey): void
    {
        WebhookValidationFailed::dispatch($error, $this->ip() ?? 'unknown', []);

        $message = config("tap.webhook.messages.{$messageKey}", $error);

        throw new HttpResponseException(
            new Response(is_string($message) ? $message : $error, 400)
        );
    }

    protected function failedValidation(Validator $validator): void
    {
        $this->failWithResponse('Invalid payload structure', 'invalid_payload');
    }
}
