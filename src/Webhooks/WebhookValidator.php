<?php

declare(strict_types=1);

namespace TapPay\Tap\Webhooks;

use Illuminate\Http\Request;

class WebhookValidator
{
    protected string $secret;

    public function __construct(?string $secret = null)
    {
        $this->secret = $secret ?? config('tap.webhook_secret') ?? config('tap.secret_key');
    }

    /**
     * Validate the webhook signature
     */
    public function validate(Request $request): bool
    {
        $signature = $request->header('x-tap-signature');

        if (!$signature) {
            return false;
        }

        $payload = $request->getContent();
        $data = json_decode($payload, true);

        if (!$data) {
            return false;
        }

        $hashString = $this->buildHashString($data);
        $computedSignature = hash_hmac('sha256', $hashString, $this->secret);

        return hash_equals($computedSignature, $signature);
    }

    /**
     * Build the hash string from webhook payload
     * This concatenates specific values from the webhook payload
     */
    protected function buildHashString(array $data): string
    {
        // According to Tap documentation, the hashstring is built from specific fields
        // The exact fields depend on the webhook type (charge, refund, etc.)
        // Common pattern: concatenate values in a specific order

        $fields = [];

        // For charge webhooks
        if (isset($data['id'])) {
            $fields[] = $data['id'];
        }

        if (isset($data['amount'])) {
            $fields[] = $data['amount'];
        }

        if (isset($data['currency'])) {
            $fields[] = $data['currency'];
        }

        if (isset($data['status'])) {
            $fields[] = $data['status'];
        }

        if (isset($data['created'])) {
            $fields[] = $data['created'];
        }

        // Join all fields
        return implode('', $fields);
    }

    /**
     * Check if the webhook is within tolerance time
     */
    public function isWithinTolerance(array $data): bool
    {
        $tolerance = config('tap.webhook_tolerance', 300); // 5 minutes default

        if (!isset($data['created'])) {
            return true; // If no timestamp, skip tolerance check
        }

        $created = $data['created'];
        $now = time();

        return abs($now - $created) <= $tolerance;
    }
}
