<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use TapPay\Tap\Events\PaymentFailed;
use TapPay\Tap\Events\PaymentSucceeded;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Http\Middleware\VerifyRedirectUrl;

class PaymentCallbackController extends Controller
{
    public function __construct()
    {
        $this->middleware(VerifyRedirectUrl::class);
    }

    public function __invoke(Request $request): RedirectResponse
    {
        $chargeId = $request->query('tap_id');
        $redirect = $request->query('redirect');
        $redirectUrl = is_string($redirect) ? $redirect : null;

        if (!$chargeId || !is_string($chargeId)) {
            return $this->redirectToFailure($redirectUrl, 'Missing or invalid tap_id');
        }

        try {
            $charge = Tap::charges()->retrieve($chargeId);
        } catch (AuthenticationException) {
            return $this->redirectToFailure($redirectUrl, 'Authentication failed');
        } catch (InvalidRequestException) {
            return $this->redirectToFailure($redirectUrl, 'Invalid charge ID');
        } catch (ApiErrorException) {
            return $this->redirectToFailure($redirectUrl, 'Failed to retrieve charge');
        }

        if ($charge->isSuccessful()) {
            PaymentSucceeded::dispatch($charge, $redirectUrl);

            return $this->redirectToSuccess($redirectUrl, $charge->id());
        }

        PaymentFailed::dispatch($charge, $redirectUrl);

        return $this->redirectToFailure($redirectUrl, $charge->get('response.message') ?? 'Payment failed');
    }

    protected function redirectToSuccess(?string $redirectUrl, string $chargeId): RedirectResponse
    {
        $successUrl = $redirectUrl
            ?? config('tap.redirect.success')
            ?? '/';

        return redirect()->to($successUrl)->with([
            'tap_charge_id' => $chargeId,
            'tap_status' => 'success',
        ]);
    }

    protected function redirectToFailure(?string $redirectUrl, string $message): RedirectResponse
    {
        $failureUrl = $redirectUrl
            ?? config('tap.redirect.failure')
            ?? '/';

        return redirect()->to($failureUrl)->with([
            'tap_status' => 'failed',
            'tap_error' => $message,
        ]);
    }
}