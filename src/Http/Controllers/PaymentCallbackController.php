<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use TapPay\Tap\Events\PaymentFailed;
use TapPay\Tap\Events\PaymentSucceeded;
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

        if (!$chargeId || !is_string($chargeId)) {
            return $this->redirectToFailure($request, 'Missing or invalid tap_id');
        }

        try {
            $charge = Tap::charges()->retrieve($chargeId);
        } catch (\Exception $e) {
            return $this->redirectToFailure($request, 'Failed to retrieve charge');
        }

        $redirectUrl = $request->query('redirect');

        if ($charge->isSuccessful()) {
            PaymentSucceeded::dispatch($charge, $redirectUrl);

            return $this->redirectToSuccess($request, $charge->id());
        }

        PaymentFailed::dispatch($charge, $redirectUrl);

        return $this->redirectToFailure($request, $charge->get('response.message') ?? 'Payment failed');
    }

    protected function redirectToSuccess(Request $request, string $chargeId): RedirectResponse
    {
        $successUrl = $request->query('redirect')
            ?? config('tap.redirect.success')
            ?? '/';

        return redirect()->to($successUrl)->with([
            'tap_charge_id' => $chargeId,
            'tap_status' => 'success',
        ]);
    }

    protected function redirectToFailure(Request $request, string $message): RedirectResponse
    {
        $failureUrl = $request->query('redirect')
            ?? config('tap.redirect.failure')
            ?? '/';

        return redirect()->to($failureUrl)->with([
            'tap_status' => 'failed',
            'tap_error' => $message,
        ]);
    }
}