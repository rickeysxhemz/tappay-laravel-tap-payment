<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use TapPay\Tap\Tests\TestCase;
use TapPay\Tap\Webhooks\WebhookController;
use TapPay\Tap\Webhooks\WebhookValidator;

class WebhookTest extends TestCase
{
    protected WebhookValidator $validator;
    protected string $secretKey = 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ';

    protected function setUp(): void
    {
        parent::setUp();

        config(['tap.secret_key' => $this->secretKey]);
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

        $this->assertTrue($this->validator->validate($request));
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
            'HTTP_X_TAP_SIGNATURE' => 'invalid_signature_here',
        ], json_encode($payload));

        $this->assertFalse($this->validator->validate($request));
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

        $this->assertFalse($this->validator->validate($request));
    }
    #[Test]
    public function it_checks_webhook_tolerance(): void
    {
        // Recent timestamp - within tolerance
        $recentPayload = ['created' => time() - 60]; // 1 minute ago
        $this->assertTrue($this->validator->isWithinTolerance($recentPayload));

        // Old timestamp - outside tolerance
        $oldPayload = ['created' => time() - 400]; // 6+ minutes ago
        $this->assertFalse($this->validator->isWithinTolerance($oldPayload));

        // No timestamp - should pass
        $noTimestampPayload = ['id' => 'test'];
        $this->assertTrue($this->validator->isWithinTolerance($noTimestampPayload));
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

        // Assert events were dispatched
        Event::assertDispatched('tap.webhook.charge');
        Event::assertDispatched('tap.webhook.received');
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

        Event::assertDispatched('tap.webhook.charge');
        Event::assertDispatched('tap.webhook.refund');
        Event::assertDispatched('tap.webhook.customer');
        Event::assertDispatched('tap.webhook.received', 3);
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