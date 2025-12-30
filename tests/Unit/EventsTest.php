<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Events\PaymentFailed;
use TapPay\Tap\Events\PaymentSucceeded;
use TapPay\Tap\Events\WebhookProcessingFailed;
use TapPay\Tap\Events\WebhookReceived;
use TapPay\Tap\Events\WebhookValidationFailed;
use TapPay\Tap\Resources\Charge;
use TapPay\Tap\Tests\TestCase;

class EventsTest extends TestCase
{
    #[Test]
    public function payment_succeeded_event_has_charge_and_redirect_url(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'CAPTURED',
            'customer' => ['id' => 'cus_test_456'],
        ]);

        $event = new PaymentSucceeded($charge, 'https://example.com/success');

        $this->assertSame($charge, $event->charge);
        $this->assertSame('https://example.com/success', $event->redirectUrl);
    }

    #[Test]
    public function payment_succeeded_event_can_get_charge_id(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'CAPTURED',
        ]);

        $event = new PaymentSucceeded($charge);

        $this->assertSame('chg_test_123', $event->getChargeId());
    }

    #[Test]
    public function payment_succeeded_event_can_get_amount(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 150.00,
            'currency' => 'SAR',
            'status' => 'CAPTURED',
        ]);

        $event = new PaymentSucceeded($charge);

        $this->assertSame(150.00, $event->getAmount()->toDecimal());
    }

    #[Test]
    public function payment_succeeded_event_can_get_currency(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'KWD',
            'status' => 'CAPTURED',
        ]);

        $event = new PaymentSucceeded($charge);

        $this->assertSame('KWD', $event->getCurrency());
    }

    #[Test]
    public function payment_succeeded_event_can_get_customer_id(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'CAPTURED',
            'customer' => ['id' => 'cus_test_789'],
        ]);

        $event = new PaymentSucceeded($charge);

        $this->assertSame('cus_test_789', $event->getCustomerId());
    }

    #[Test]
    public function payment_succeeded_event_returns_null_for_missing_customer(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'CAPTURED',
        ]);

        $event = new PaymentSucceeded($charge);

        $this->assertNull($event->getCustomerId());
    }

    #[Test]
    public function payment_failed_event_has_charge_and_redirect_url(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'FAILED',
        ]);

        $event = new PaymentFailed($charge, 'https://example.com/failure');

        $this->assertSame($charge, $event->charge);
        $this->assertSame('https://example.com/failure', $event->redirectUrl);
    }

    #[Test]
    public function payment_failed_event_can_get_charge_id(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_failed',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'FAILED',
        ]);

        $event = new PaymentFailed($charge);

        $this->assertSame('chg_test_failed', $event->getChargeId());
    }

    #[Test]
    public function payment_failed_event_can_get_status(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'DECLINED',
        ]);

        $event = new PaymentFailed($charge);

        $this->assertSame('DECLINED', $event->getStatus());
    }

    #[Test]
    public function payment_failed_event_can_get_response_code(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'FAILED',
            'response' => [
                'code' => '001',
                'message' => 'Insufficient funds',
            ],
        ]);

        $event = new PaymentFailed($charge);

        $this->assertSame('001', $event->getResponseCode());
    }

    #[Test]
    public function payment_failed_event_can_get_response_message(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'FAILED',
            'response' => [
                'code' => '001',
                'message' => 'Insufficient funds',
            ],
        ]);

        $event = new PaymentFailed($charge);

        $this->assertSame('Insufficient funds', $event->getResponseMessage());
    }

    #[Test]
    public function payment_failed_event_returns_null_for_missing_response_code(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'FAILED',
        ]);

        $event = new PaymentFailed($charge);

        $this->assertNull($event->getResponseCode());
    }

    #[Test]
    public function payment_failed_event_returns_null_for_missing_response_message(): void
    {
        $charge = new Charge([
            'id' => 'chg_test_123',
            'amount' => 100.00,
            'currency' => 'SAR',
            'status' => 'FAILED',
        ]);

        $event = new PaymentFailed($charge);

        $this->assertNull($event->getResponseMessage());
    }

    #[Test]
    public function webhook_received_event_has_payload_and_resource_type(): void
    {
        $payload = [
            'id' => 'chg_test_123',
            'object' => 'CHARGE',
            'status' => 'CAPTURED',
        ];

        $event = new WebhookReceived('CHARGE', $payload, '127.0.0.1');

        $this->assertSame($payload, $event->payload);
        $this->assertSame('CHARGE', $event->resource);
        $this->assertSame('127.0.0.1', $event->ip);
    }

    #[Test]
    public function webhook_received_event_can_get_id(): void
    {
        $payload = ['id' => 'chg_test_123'];

        $event = new WebhookReceived('CHARGE', $payload, '127.0.0.1');

        $this->assertSame('chg_test_123', $event->getId());
    }

    #[Test]
    public function webhook_received_event_is_type_check(): void
    {
        $event = new WebhookReceived('CHARGE', [], '127.0.0.1');

        $this->assertTrue($event->isType('CHARGE'));
        $this->assertFalse($event->isType('REFUND'));
    }

    #[Test]
    public function webhook_validation_failed_event_has_reason_and_ip(): void
    {
        $event = new WebhookValidationFailed('Invalid signature', '192.168.1.1');

        $this->assertSame('Invalid signature', $event->reason);
        $this->assertSame('192.168.1.1', $event->ip);
    }

    #[Test]
    public function webhook_validation_failed_event_has_context(): void
    {
        $context = ['header' => 'missing'];

        $event = new WebhookValidationFailed('Missing header', '127.0.0.1', $context);

        $this->assertSame('Missing header', $event->reason);
        $this->assertSame($context, $event->context);
    }

    #[Test]
    public function webhook_validation_failed_event_from_request(): void
    {
        $request = \Illuminate\Http\Request::create(
            'https://example.com/webhook',
            'POST',
            [],
            [],
            [],
            ['REMOTE_ADDR' => '192.168.1.100']
        );

        $event = WebhookValidationFailed::fromRequest('Invalid signature', $request, ['extra' => 'data']);

        $this->assertSame('Invalid signature', $event->reason);
        $this->assertSame('192.168.1.100', $event->ip);
        $this->assertArrayHasKey('url', $event->context);
        $this->assertArrayHasKey('method', $event->context);
        $this->assertArrayHasKey('extra', $event->context);
        $this->assertSame('POST', $event->context['method']);
        $this->assertSame('data', $event->context['extra']);
    }

    #[Test]
    public function webhook_processing_failed_event_has_exception_and_payload(): void
    {
        $exception = new \RuntimeException('Processing error');
        $payload = ['id' => 'chg_test_123'];

        $event = new WebhookProcessingFailed($exception, 'CHARGE', $payload);

        $this->assertSame($exception, $event->exception);
        $this->assertSame('CHARGE', $event->resource);
        $this->assertSame($payload, $event->payload);
    }

    #[Test]
    public function webhook_processing_failed_event_can_get_id(): void
    {
        $exception = new \RuntimeException('Error');
        $payload = ['id' => 'chg_test_456'];

        $event = new WebhookProcessingFailed($exception, 'CHARGE', $payload);

        $this->assertSame('chg_test_456', $event->getId());
    }

    #[Test]
    public function webhook_processing_failed_event_can_get_error_message(): void
    {
        $exception = new \RuntimeException('Something went wrong');
        $payload = [];

        $event = new WebhookProcessingFailed($exception, 'CHARGE', $payload);

        $this->assertSame('Something went wrong', $event->getErrorMessage());
    }
}
