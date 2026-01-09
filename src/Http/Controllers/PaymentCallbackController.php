<?php

declare(strict_types=1);

namespace TapPay\Tap\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller;
use TapPay\Tap\Http\Handlers\PaymentCallbackHandler;
use TapPay\Tap\Http\Middleware\VerifyRedirectUrl;
use TapPay\Tap\Http\Requests\PaymentCallbackRequest;

use function config;
use function is_string;
use function redirect;

class PaymentCallbackController extends Controller
{
    public function __construct(
        protected PaymentCallbackHandler $handler
    ) {
        $this->middleware(VerifyRedirectUrl::class);
    }

    public function __invoke(PaymentCallbackRequest $request): RedirectResponse
    {
        $tapId = $request->tapId();

        if ($tapId === null) {
            return $this->redirectToFailure($request->redirectUrl(), 'Missing or invalid tap_id');
        }

        $result = $this->handler->handle($tapId, $request->redirectUrl());

        return $result->success
            ? $this->redirectToSuccess($request->redirectUrl(), $result->charge?->id() ?? '')
            : $this->redirectToFailure($request->redirectUrl(), $result->error ?? 'Payment failed');
    }

    protected function redirectToSuccess(?string $redirectUrl, string $chargeId): RedirectResponse
    {
        $url = $redirectUrl ?? config('tap.redirect.success') ?? '/';

        return redirect()->to($this->ensureString($url))->with([
            'tap_charge_id' => $chargeId,
            'tap_status' => 'success',
        ]);
    }

    protected function redirectToFailure(?string $redirectUrl, string $message): RedirectResponse
    {
        $url = $redirectUrl ?? config('tap.redirect.failure') ?? '/';

        return redirect()->to($this->ensureString($url))->with([
            'tap_status' => 'failed',
            'tap_error' => $message,
        ]);
    }

    private function ensureString(mixed $value): string
    {
        return is_string($value) ? $value : '/';
    }
}
