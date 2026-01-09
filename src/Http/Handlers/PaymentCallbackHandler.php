<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Handlers;

use TapPay\Tap\Events\PaymentFailed;
use TapPay\Tap\Events\PaymentRetrievalFailed;
use TapPay\Tap\Events\PaymentSucceeded;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Charge;

use function is_string;

class PaymentCallbackHandler
{
    public function handle(string $chargeId, ?string $redirectUrl): PaymentCallbackResult
    {
        $result = $this->retrieveCharge($chargeId, $redirectUrl);

        if ($result['charge'] === null) {
            return PaymentCallbackResult::failed(null, $result['error'] ?? 'Failed to retrieve charge');
        }

        $charge = $result['charge'];

        if ($charge->isSuccessful()) {
            PaymentSucceeded::dispatch($charge, $redirectUrl);

            return PaymentCallbackResult::success($charge);
        }

        PaymentFailed::dispatch($charge, $redirectUrl);

        $responseMessage = $charge->get('response.message');
        $error = is_string($responseMessage) ? $responseMessage : 'Payment failed';

        return PaymentCallbackResult::failed($charge, $error);
    }

    /**
     * @return array{charge: Charge|null, error: string|null}
     */
    protected function retrieveCharge(string $chargeId, ?string $redirectUrl): array
    {
        try {
            $charge = Tap::charges()->retrieve($chargeId);

            if (! $charge instanceof Charge) {
                return ['charge' => null, 'error' => 'Invalid charge response'];
            }

            return ['charge' => $charge, 'error' => null];
        } catch (AuthenticationException $e) {
            PaymentRetrievalFailed::dispatch(
                $chargeId,
                PaymentRetrievalFailed::ERROR_TYPE_AUTHENTICATION,
                'Authentication failed',
                $e,
                $redirectUrl
            );

            return ['charge' => null, 'error' => 'Authentication failed'];
        } catch (InvalidRequestException $e) {
            PaymentRetrievalFailed::dispatch(
                $chargeId,
                PaymentRetrievalFailed::ERROR_TYPE_INVALID_REQUEST,
                'Invalid charge ID',
                $e,
                $redirectUrl
            );

            return ['charge' => null, 'error' => 'Invalid charge ID'];
        } catch (ApiErrorException $e) {
            PaymentRetrievalFailed::dispatch(
                $chargeId,
                PaymentRetrievalFailed::ERROR_TYPE_API_ERROR,
                'Failed to retrieve charge',
                $e,
                $redirectUrl
            );

            return ['charge' => null, 'error' => 'Failed to retrieve charge'];
        }
    }
}
