<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Events\WebhookProcessingFailed;
use TapPay\Tap\Events\WebhookReceived;
use TapPay\Tap\Http\Handlers\WebhookHandler;
use TapPay\Tap\Tests\TestCase;
use TapPay\Tap\Webhooks\WebhookValidator;

class WebhookTest extends TestCase
{
    protected WebhookValidator $validator;

    protected WebhookHandler $handler;

    protected string $secretKey = 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ';

    protected function setUp(): void
    {
        parent::setUp();

        config(['tap.secret' => $this->secretKey]);
        $this->validator = new WebhookValidator($this->secretKey);
        $this->handler = new WebhookHandler;
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
        $signature = str_repeat('a', 64);

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
        $signature = str_repeat('a', 64);

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
    public function it_rejects_invalid_payload_with_validate_payload(): void
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
        $recentPayload = ['created' => time() - 60];
        $this->assertTrue($this->validator->checkTolerance($recentPayload)->isValid());

        $oldPayload = ['created' => time() - 400];
        $this->assertFalse($this->validator->checkTolerance($oldPayload)->isValid());
    }

    #[Test]
    public function it_rejects_webhook_without_timestamp(): void
    {
        $noTimestampPayload = ['id' => 'test'];
        $result = $this->validator->checkTolerance($noTimestampPayload);

        $this->assertFalse($result->isValid());
        $this->assertSame('Missing created timestamp', $result->getError());
    }

    #[Test]
    public function it_handles_webhook_payload_successfully(): void
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

        $this->handler->handle($payload, '127.0.0.1');

        Event::assertDispatched('tap.webhook.charge');
        Event::assertDispatched('tap.webhook.received');

        Event::assertDispatched(WebhookReceived::class, function ($event) use ($payload) {
            return $event->resource === 'charge'
                && $event->payload === $payload
                && $event->getId() === 'chg_test_123';
        });
    }

    #[Test]
    public function it_dispatches_events_for_different_resource_types(): void
    {
        Event::fake();

        $payloads = [
            ['object' => 'charge', 'id' => 'chg_123', 'amount' => 10.50, 'currency' => 'USD', 'status' => 'CAPTURED', 'created' => time()],
            ['object' => 'refund', 'id' => 'ref_456', 'amount' => 5.00, 'currency' => 'USD', 'status' => 'SUCCEEDED', 'created' => time()],
            ['object' => 'customer', 'id' => 'cus_789', 'amount' => 0, 'currency' => 'USD', 'status' => 'ACTIVE', 'created' => time()],
        ];

        foreach ($payloads as $payload) {
            $this->handler->handle($payload, '127.0.0.1');
        }

        Event::assertDispatched('tap.webhook.charge');
        Event::assertDispatched('tap.webhook.refund');
        Event::assertDispatched('tap.webhook.customer');
        Event::assertDispatched('tap.webhook.received', 3);
        Event::assertDispatched(WebhookReceived::class, 3);
    }

    #[Test]
    public function it_skips_unknown_resource_types(): void
    {
        Event::fake();

        $payload = [
            'object' => 'unknown_resource',
            'id' => 'unk_123',
            'amount' => 10.00,
            'currency' => 'USD',
            'status' => 'UNKNOWN',
            'created' => time(),
        ];

        $this->handler->handle($payload, '127.0.0.1');

        Event::assertNotDispatched('tap.webhook.unknown_resource');
        Event::assertDispatched('tap.webhook.received');
        Event::assertDispatched(WebhookReceived::class, function ($event) {
            return $event->resource === 'unknown_resource';
        });
    }

    #[Test]
    public function it_handles_event_dispatch_errors_gracefully(): void
    {
        Event::listen('tap.webhook.charge', function () {
            throw new \Exception('Event handler failed');
        });

        Event::fake([WebhookProcessingFailed::class]);

        $payload = [
            'object' => 'charge',
            'id' => 'chg_123',
            'amount' => 10.50,
            'currency' => 'USD',
            'status' => 'CAPTURED',
            'created' => time(),
        ];

        $this->handler->handle($payload, '127.0.0.1');

        Event::assertDispatched(WebhookProcessingFailed::class, function ($event) {
            return $event->resource === 'charge'
                && $event->exception->getMessage() === 'Event handler failed'
                && $event->getId() === 'chg_123';
        });
    }

    /**
     * @param  array<string, mixed>  $payload
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
