<?php

use Illuminate\Support\Facades\Route;
use TapPay\Tap\Http\Controllers\PaymentCallbackController;
use TapPay\Tap\Http\Controllers\WebhookController;

Route::get('callback', PaymentCallbackController::class)->name('callback');

Route::post('webhook', WebhookController::class)->name('webhook');