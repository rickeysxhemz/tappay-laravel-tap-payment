<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Events\PaymentRetrievalFailed;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Tests\TestCase;

class PaymentRetrievalFailedEventTest extends TestCase
{
    #[Test]
    public function it_creates_event_with_all_properties(): void
    {
        $exception = new \Exception('Test exception');

        $event = new PaymentRetrievalFailed(
            chargeId: 'chg_test_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_API_ERROR,
            errorMessage: 'Failed to retrieve charge',
            exception: $exception,
            redirectUrl: '/payment/failed'
        );

        $this->assertSame('chg_test_123', $event->chargeId);
        $this->assertSame(PaymentRetrievalFailed::ERROR_TYPE_API_ERROR, $event->errorType);
        $this->assertSame('Failed to retrieve charge', $event->errorMessage);
        $this->assertSame($exception, $event->exception);
        $this->assertSame('/payment/failed', $event->redirectUrl);
    }

    #[Test]
    public function it_creates_event_with_nullable_properties(): void
    {
        $event = new PaymentRetrievalFailed(
            chargeId: 'chg_test_456',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_AUTHENTICATION,
            errorMessage: 'Authentication failed'
        );

        $this->assertSame('chg_test_456', $event->chargeId);
        $this->assertNull($event->exception);
        $this->assertNull($event->redirectUrl);
    }

    #[Test]
    public function it_correctly_identifies_authentication_error(): void
    {
        $event = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_AUTHENTICATION,
            errorMessage: 'Authentication failed'
        );

        $this->assertTrue($event->isAuthenticationError());
        $this->assertFalse($event->isInvalidRequestError());
        $this->assertFalse($event->isApiError());
    }

    #[Test]
    public function it_correctly_identifies_invalid_request_error(): void
    {
        $event = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_INVALID_REQUEST,
            errorMessage: 'Invalid charge ID'
        );

        $this->assertFalse($event->isAuthenticationError());
        $this->assertTrue($event->isInvalidRequestError());
        $this->assertFalse($event->isApiError());
    }

    #[Test]
    public function it_correctly_identifies_api_error(): void
    {
        $event = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_API_ERROR,
            errorMessage: 'API error'
        );

        $this->assertFalse($event->isAuthenticationError());
        $this->assertFalse($event->isInvalidRequestError());
        $this->assertTrue($event->isApiError());
    }

    #[Test]
    public function it_correctly_identifies_configuration_issue(): void
    {
        $authEvent = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_AUTHENTICATION,
            errorMessage: 'Authentication failed'
        );

        $apiEvent = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_API_ERROR,
            errorMessage: 'API error'
        );

        $this->assertTrue($authEvent->isConfigurationIssue());
        $this->assertFalse($apiEvent->isConfigurationIssue());
    }

    #[Test]
    public function it_correctly_identifies_infrastructure_issue(): void
    {
        $apiEvent = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_API_ERROR,
            errorMessage: 'API error'
        );

        $authEvent = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_AUTHENTICATION,
            errorMessage: 'Authentication failed'
        );

        $this->assertTrue($apiEvent->isInfrastructureIssue());
        $this->assertFalse($authEvent->isInfrastructureIssue());
    }

    #[Test]
    public function it_stores_authentication_exception(): void
    {
        $exception = new AuthenticationException('Invalid API key');

        $event = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_AUTHENTICATION,
            errorMessage: 'Authentication failed',
            exception: $exception
        );

        $this->assertInstanceOf(AuthenticationException::class, $event->exception);
        $this->assertSame('Invalid API key', $event->exception->getMessage());
    }

    #[Test]
    public function it_stores_invalid_request_exception(): void
    {
        $exception = new InvalidRequestException('Charge not found');

        $event = new PaymentRetrievalFailed(
            chargeId: 'chg_invalid',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_INVALID_REQUEST,
            errorMessage: 'Invalid charge ID',
            exception: $exception
        );

        $this->assertInstanceOf(InvalidRequestException::class, $event->exception);
        $this->assertSame('Charge not found', $event->exception->getMessage());
    }

    #[Test]
    public function it_stores_api_error_exception(): void
    {
        $exception = new ApiErrorException('Service unavailable');

        $event = new PaymentRetrievalFailed(
            chargeId: 'chg_123',
            errorType: PaymentRetrievalFailed::ERROR_TYPE_API_ERROR,
            errorMessage: 'Failed to retrieve charge',
            exception: $exception
        );

        $this->assertInstanceOf(ApiErrorException::class, $event->exception);
        $this->assertSame('Service unavailable', $event->exception->getMessage());
    }

    #[Test]
    public function it_has_correct_error_type_constants(): void
    {
        $this->assertSame('authentication', PaymentRetrievalFailed::ERROR_TYPE_AUTHENTICATION);
        $this->assertSame('invalid_request', PaymentRetrievalFailed::ERROR_TYPE_INVALID_REQUEST);
        $this->assertSame('api_error', PaymentRetrievalFailed::ERROR_TYPE_API_ERROR);
    }

    #[Test]
    public function it_uses_dispatchable_trait(): void
    {
        $this->assertTrue(
            method_exists(PaymentRetrievalFailed::class, 'dispatch'),
            'PaymentRetrievalFailed should have dispatch method from Dispatchable trait'
        );
    }
}
