<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use TapPay\Tap\Http\Controllers\PaymentCallbackController;
use TapPay\Tap\Http\Controllers\WebhookController;
use TapPay\Tap\Http\Middleware\VerifyRedirectUrl;

Route::get('callback', PaymentCallbackController::class)
    ->middleware(VerifyRedirectUrl::class)
    ->name('callback');

Route::post('webhook', WebhookController::class)->name('webhook');
