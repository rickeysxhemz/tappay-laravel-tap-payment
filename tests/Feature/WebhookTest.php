<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use TapPay\Tap\Events\WebhookProcessingFailed;
use TapPay\Tap\Events\WebhookReceived;
use TapPay\Tap\Events\WebhookValidationFailed;
use TapPay\Tap\Tests\TestCase;
use TapPay\Tap\Http\Controllers\WebhookController;
use TapPay\Tap\Webhooks\WebhookValidator;

class WebhookTest extends TestCase
{
    protected WebhookValidator $validator;
    protected string $secretKey = 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ';

    protected function setUp(): void
    {
        parent::setUp();

        config(['tap.secret' => $this->secretKey]);
        $this->validator = new WebhookValidator($this->secretKey);
    }
    #[Test]
    public function it_validates_webhook_signature_correctly(): void
    {
        $payload = [
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'created' => time(),
        ];

        $signature = $this->generateSignature($payload);

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => $signature,
        ], json_encode($payload));

        $result = $this->validator->validate($request);
        $this->assertTrue($result->isValid());
    }
    #[Test]
    public function it_rejects_invalid_signature(): void
    {
        $payload = [
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'created' => time(),
        ];

        // Use a 64-character invalid signature to trigger signature mismatch (not length error)
        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => str_repeat('a', 64),
        ], json_encode($payload));

        $result = $this->validator->validate($request);
        $this->assertFalse($result->isValid());
        $this->assertSame('Signature mismatch', $result->getError());
    }
    #[Test]
    public function it_rejects_webhook_without_signature(): void
    {
        $payload = [
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
        ];

        $request = Request::create('/webhook', 'POST', [], [], [], [], json_encode($payload));

        $result = $this->validator->validate($request);
        $this->assertFalse($result->isValid());
        $this->assertSame('Missing or invalid signature length', $result->getError());
    }

    #[Test]
    public function it_rejects_webhook_with_invalid_signature_length(): void
    {
        $payload = [
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
        ];

        // Invalid signature - too short
        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => 'invalid_short_signature',
        ], json_encode($payload));

        $result = $this->validator->validate($request);
        $this->assertFalse($result->isValid());
        $this->assertSame('Missing or invalid signature length', $result->getError());
    }

    #[Test]
    public function it_rejects_webhook_with_empty_payload(): void
    {
        $signature = str_repeat('a', 64); // Valid length, wrong signature

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => $signature,
        ], '');

        $result = $this->validator->validate($request);
        $this->assertFalse($result->isValid());
        $this->assertSame('Empty payload', $result->getError());
    }

    #[Test]
    public function it_rejects_webhook_with_invalid_json(): void
    {
        $signature = str_repeat('a', 64); // Valid length, wrong signature

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => $signature,
        ], '{invalid json}');

        $result = $this->validator->validate($request);
        $this->assertFalse($result->isValid());
        $this->assertSame('Invalid JSON', $result->getError());
    }

    #[Test]
    public function it_validates_payload_directly(): void
    {
        $payload = [
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'created' => time(),
        ];

        $signature = $this->generateSignature($payload);

        $result = $this->validator->validatePayload($payload, $signature);
        $this->assertTrue($result->isValid());
    }

    #[Test]
    public function it_rejects_invalid_payload_with_validatePayload(): void
    {
        $payload = [
            'id' => 'chg_test_123',
            'amount' => 10.50,
        ];

        $result = $this->validator->validatePayload($payload, 'wrong_signature');
        $this->assertFalse($result->isValid());
    }
    #[Test]
    public function it_checks_webhook_tolerance(): void
    {
        // Recent timestamp - within tolerance
        $recentPayload = ['created' => time() - 60]; // 1 minute ago
        $this->assertTrue($this->validator->checkTolerance($recentPayload)->isValid());

        // Old timestamp - outside tolerance
        $oldPayload = ['created' => time() - 400]; // 6+ minutes ago
        $this->assertFalse($this->validator->checkTolerance($oldPayload)->isValid());
    }

    #[Test]
    public function it_rejects_webhook_without_timestamp(): void
    {
        // No timestamp - should be rejected to prevent replay attacks
        $noTimestampPayload = ['id' => 'test'];
        $result = $this->validator->checkTolerance($noTimestampPayload);

        $this->assertFalse($result->isValid());
        $this->assertSame('Missing created timestamp', $result->getError());
    }
    #[Test]
    public function it_handles_webhook_request_successfully(): void
    {
        Event::fake();

        $payload = [
            'object' => 'charge',
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'created' => time(),
        ];

        $signature = $this->generateSignature($payload);

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => $signature,
        ], json_encode($payload));

        $controller = new WebhookController($this->validator);
        $response = $controller($request);

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('Webhook received', $response->getContent());

        // Assert string-based events were dispatched
        Event::assertDispatched('tap.webhook.charge');
        Event::assertDispatched('tap.webhook.received');

        // Assert WebhookReceived event class was dispatched
        Event::assertDispatched(WebhookReceived::class, function ($event) use ($payload) {
            return $event->resource === 'charge'
                && $event->payload === $payload
                && $event->getId() === 'chg_test_123';
        });
    }
    #[Test]
    public function it_rejects_webhook_with_invalid_signature(): void
    {
        $payload = [
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'created' => time(),
        ];

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => 'wrong_signature',
        ], json_encode($payload));

        $controller = new WebhookController($this->validator);
        $response = $controller($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Invalid signature', $response->getContent());
    }
    #[Test]
    public function it_rejects_expired_webhook(): void
    {
        $payload = [
            'object' => 'charge',
            'id' => 'chg_test_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'created' => time() - 400, // 6+ minutes ago
        ];

        $signature = $this->generateSignature($payload);

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => $signature,
        ], json_encode($payload));

        $controller = new WebhookController($this->validator);
        $response = $controller($request);

        $this->assertEquals(400, $response->getStatusCode());
        $this->assertEquals('Webhook expired', $response->getContent());
    }
    #[Test]
    public function it_dispatches_events_for_different_resource_types(): void
    {
        Event::fake();

        $payloads = [
            ['object' => 'charge', 'id' => 'chg_123'],
            ['object' => 'refund', 'id' => 'ref_456'],
            ['object' => 'customer', 'id' => 'cus_789'],
        ];

        foreach ($payloads as $payload) {
            $payload['created'] = time();
            $signature = $this->generateSignature($payload);

            $request = Request::create('/webhook', 'POST', [], [], [], [
                'HTTP_X_TAP_SIGNATURE' => $signature,
            ], json_encode($payload));

            $controller = new WebhookController($this->validator);
            $controller($request);
        }

        // Assert string-based events
        Event::assertDispatched('tap.webhook.charge');
        Event::assertDispatched('tap.webhook.refund');
        Event::assertDispatched('tap.webhook.customer');
        Event::assertDispatched('tap.webhook.received', 3);

        // Assert WebhookReceived event class was dispatched 3 times
        Event::assertDispatched(WebhookReceived::class, 3);
    }

    #[Test]
    public function it_rejects_unknown_resource_types(): void
    {
        Event::fake();

        $payload = [
            'object' => 'unknown_resource',
            'id' => 'unk_123',
            'created' => time(),
        ];

        $signature = $this->generateSignature($payload);

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => $signature,
        ], json_encode($payload));

        $controller = new WebhookController($this->validator);
        $response = $controller($request);

        // Should still return 200 (to prevent retries)
        $this->assertEquals(200, $response->getStatusCode());

        // Should dispatch general event but not resource-specific
        Event::assertNotDispatched('tap.webhook.unknown_resource');
        Event::assertDispatched('tap.webhook.received');

        // Assert WebhookReceived event class was still dispatched
        Event::assertDispatched(WebhookReceived::class, function ($event) {
            return $event->resource === 'unknown_resource';
        });
    }

    #[Test]
    public function it_handles_event_dispatch_errors_gracefully(): void
    {
        // Set up listener BEFORE faking events
        Event::listen('tap.webhook.charge', function () {
            throw new \Exception('Event handler failed');
        });

        // Only fake WebhookProcessingFailed to allow real exception to be thrown
        Event::fake([WebhookProcessingFailed::class]);

        $payload = [
            'object' => 'charge',
            'id' => 'chg_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'created' => time(),
        ];

        $signature = $this->generateSignature($payload);

        $request = Request::create('/webhook', 'POST', [], [], [], [
            'HTTP_X_TAP_SIGNATURE' => $signature,
        ], json_encode($payload));

        $controller = new WebhookController($this->validator);
        $response = $controller($request);

        // Should still return 200 despite error
        $this->assertEquals(200, $response->getStatusCode());

        // Assert WebhookProcessingFailed event was dispatched
        Event::assertDispatched(WebhookProcessingFailed::class, function ($event) {
            return $event->resource === 'charge'
                && $event->exception->getMessage() === 'Event handler failed'
                && $event->getId() === 'chg_123';
        });
    }

    /**
     * Generate HMAC signature for webhook payload
     */
    protected function generateSignature(array $payload): string
    {
        $fields = [];

        if (isset($payload['id'])) {
            $fields[] = $payload['id'];
        }
        if (isset($payload['amount'])) {
            $fields[] = $payload['amount'];
        }
        if (isset($payload['currency'])) {
            $fields[] = $payload['currency'];
        }
        if (isset($payload['status'])) {
            $fields[] = $payload['status'];
        }
        if (isset($payload['created'])) {
            $fields[] = $payload['created'];
        }

        $hashString = implode('', $fields);

        return hash_hmac('sha256', $hashString, $this->secretKey);
    }
}