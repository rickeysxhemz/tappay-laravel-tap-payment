<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Route;
use TapPay\Tap\Events\PaymentFailed;
use TapPay\Tap\Events\PaymentSucceeded;
use TapPay\Tap\Events\WebhookReceived;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Facades\Tap;
use TapPay\Tap\Resources\Charge;

beforeEach(function () {
    config(['tap.secret' => 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ']);
    config(['tap.webhook.secret' => 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ']);
});

// Route Registration Tests
test('callback route is registered', function () {
    $route = Route::getRoutes()->getByName('tap.callback');

    expect($route)->not->toBeNull()
        ->and($route->methods())->toContain('GET')
        ->and($route->uri())->toBe('tap/callback');
})->group('feature', 'routes');

test('webhook route is registered', function () {
    $route = Route::getRoutes()->getByName('tap.webhook');

    expect($route)->not->toBeNull()
        ->and($route->methods())->toContain('POST')
        ->and($route->uri())->toBe('tap/webhook');
})->group('feature', 'routes');

// Callback Route Tests
test('callback route redirects to failure when tap_id is missing', function () {
    $response = $this->get(route('tap.callback'));

    $response->assertRedirect('/');
    $response->assertSessionHas('tap_status', 'failed');
    $response->assertSessionHas('tap_error', 'Missing or invalid tap_id');
})->group('feature', 'routes', 'callback');

test('callback route redirects to failure when tap_id is empty', function () {
    $response = $this->get(route('tap.callback', ['tap_id' => '']));

    $response->assertRedirect('/');
    $response->assertSessionHas('tap_status', 'failed');
    $response->assertSessionHas('tap_error', 'Missing or invalid tap_id');
})->group('feature', 'routes', 'callback');

test('callback route redirects to custom failure url from query', function () {
    $response = $this->get(route('tap.callback', ['redirect' => '/payment/failed']));

    $response->assertRedirect('/payment/failed');
    $response->assertSessionHas('tap_status', 'failed');
})->group('feature', 'routes', 'callback');

test('callback route redirects to configured failure url', function () {
    config(['tap.redirect.failure' => '/custom-failure']);

    $response = $this->get(route('tap.callback'));

    $response->assertRedirect('/custom-failure');
})->group('feature', 'routes', 'callback');

test('callback route blocks external redirect urls', function () {
    $response = $this->get(route('tap.callback', [
        'tap_id' => 'chg_123',
        'redirect' => 'https://evil.com/steal',
    ]));

    $response->assertStatus(403);
})->group('feature', 'routes', 'callback');

test('callback route allows same-host redirect urls', function () {
    Tap::shouldReceive('charges->retrieve')
        ->once()
        ->with('chg_123')
        ->andReturn(new Charge(['id' => 'chg_123', 'status' => 'FAILED']));

    $response = $this->get(route('tap.callback', [
        'tap_id' => 'chg_123',
        'redirect' => '/dashboard',
    ]));

    $response->assertRedirect('/dashboard');
})->group('feature', 'routes', 'callback');

test('callback route dispatches PaymentSucceeded event on successful charge', function () {
    Event::fake([PaymentSucceeded::class]);

    Tap::shouldReceive('charges->retrieve')
        ->once()
        ->with('chg_123')
        ->andReturn(new Charge([
            'id' => 'chg_123',
            'status' => 'CAPTURED',
        ]));

    $response = $this->get(route('tap.callback', ['tap_id' => 'chg_123']));

    $response->assertRedirect('/');
    $response->assertSessionHas('tap_status', 'success');
    $response->assertSessionHas('tap_charge_id', 'chg_123');

    Event::assertDispatched(PaymentSucceeded::class, function ($event) {
        return $event->charge->id() === 'chg_123';
    });
})->group('feature', 'routes', 'callback');

test('callback route dispatches PaymentFailed event on failed charge', function () {
    Event::fake([PaymentFailed::class]);

    Tap::shouldReceive('charges->retrieve')
        ->once()
        ->with('chg_456')
        ->andReturn(new Charge([
            'id' => 'chg_456',
            'status' => 'FAILED',
            'response' => ['message' => 'Card declined'],
        ]));

    $response = $this->get(route('tap.callback', ['tap_id' => 'chg_456']));

    $response->assertRedirect('/');
    $response->assertSessionHas('tap_status', 'failed');
    $response->assertSessionHas('tap_error', 'Card declined');

    Event::assertDispatched(PaymentFailed::class, function ($event) {
        return $event->charge->id() === 'chg_456';
    });
})->group('feature', 'routes', 'callback');

test('callback route redirects to success url with charge id', function () {
    config(['tap.redirect.success' => '/payment/success']);

    Tap::shouldReceive('charges->retrieve')
        ->once()
        ->andReturn(new Charge([
            'id' => 'chg_789',
            'status' => 'CAPTURED',
        ]));

    $response = $this->get(route('tap.callback', ['tap_id' => 'chg_789']));

    $response->assertRedirect('/payment/success');
    $response->assertSessionHas('tap_charge_id', 'chg_789');
})->group('feature', 'routes', 'callback');

test('callback route handles api exception gracefully', function () {
    Tap::shouldReceive('charges->retrieve')
        ->once()
        ->andThrow(new ApiErrorException('API Error'));

    $response = $this->get(route('tap.callback', ['tap_id' => 'chg_invalid']));

    $response->assertRedirect('/');
    $response->assertSessionHas('tap_status', 'failed');
    $response->assertSessionHas('tap_error', 'Failed to retrieve charge');
})->group('feature', 'routes', 'callback');

// Webhook Route Tests
test('webhook route returns 400 for invalid json', function () {
    $response = $this->post(route('tap.webhook'), [], [
        'Content-Type' => 'application/json',
    ]);

    $response->assertStatus(400);
})->group('feature', 'routes', 'webhook');

test('webhook route returns 400 for missing signature', function () {
    $response = $this->postJson(route('tap.webhook'), [
        'id' => 'chg_123',
        'object' => 'charge',
        'created' => time(),
    ]);

    $response->assertStatus(400);
    $response->assertSee('Invalid signature');
})->group('feature', 'routes', 'webhook');

test('webhook route returns 400 for invalid signature', function () {
    $response = $this->postJson(route('tap.webhook'), [
        'id' => 'chg_123',
        'object' => 'charge',
        'created' => time(),
    ], [
        'x-tap-signature' => 'invalid_signature',
    ]);

    $response->assertStatus(400);
    $response->assertSee('Invalid signature');
})->group('feature', 'routes', 'webhook');

test('webhook route returns 400 for expired webhook', function () {
    $payload = [
        'id' => 'chg_123',
        'object' => 'charge',
        'amount' => 10.5,
        'currency' => 'USD',
        'status' => 'CAPTURED',
        'created' => time() - 400, // 6+ minutes ago
    ];

    $jsonPayload = json_encode($payload);
    $signature = generateWebhookSignatureFromJson($jsonPayload);

    $response = $this->call('POST', route('tap.webhook'), [], [], [], [
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_X_TAP_SIGNATURE' => $signature,
    ], $jsonPayload);

    $response->assertStatus(400);
    $response->assertSee('Webhook expired');
})->group('feature', 'routes', 'webhook');

test('webhook route returns 200 for valid webhook', function () {
    Event::fake([WebhookReceived::class]);

    $payload = [
        'id' => 'chg_123',
        'object' => 'charge',
        'amount' => 10.5,
        'currency' => 'USD',
        'status' => 'CAPTURED',
        'created' => time(),
    ];

    $jsonPayload = json_encode($payload);
    $signature = generateWebhookSignatureFromJson($jsonPayload);

    $response = $this->call('POST', route('tap.webhook'), [], [], [], [
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_X_TAP_SIGNATURE' => $signature,
    ], $jsonPayload);

    $response->assertStatus(200);
    $response->assertSee('Webhook received');

    Event::assertDispatched(WebhookReceived::class, function ($event) {
        return $event->resource === 'charge'
            && $event->getId() === 'chg_123';
    });
})->group('feature', 'routes', 'webhook');

test('webhook route dispatches resource-specific events', function () {
    Event::fake();

    $payload = [
        'id' => 'ref_123',
        'object' => 'refund',
        'amount' => 5.0,
        'currency' => 'USD',
        'status' => 'SUCCEEDED',
        'created' => time(),
    ];

    $jsonPayload = json_encode($payload);
    $signature = generateWebhookSignatureFromJson($jsonPayload);

    $response = $this->call('POST', route('tap.webhook'), [], [], [], [
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_X_TAP_SIGNATURE' => $signature,
    ], $jsonPayload);

    $response->assertStatus(200);

    Event::assertDispatched('tap.webhook.refund');
    Event::assertDispatched('tap.webhook.received');
})->group('feature', 'routes', 'webhook');

test('webhook route does not dispatch events for unknown resources', function () {
    Event::fake();

    $payload = [
        'id' => 'unk_123',
        'object' => 'unknown_type',
        'created' => time(),
    ];

    $jsonPayload = json_encode($payload);
    $signature = generateWebhookSignatureFromJson($jsonPayload);

    $response = $this->call('POST', route('tap.webhook'), [], [], [], [
        'HTTP_CONTENT_TYPE' => 'application/json',
        'HTTP_X_TAP_SIGNATURE' => $signature,
    ], $jsonPayload);

    $response->assertStatus(200);

    Event::assertNotDispatched('tap.webhook.unknown_type');
    Event::assertDispatched('tap.webhook.received');
})->group('feature', 'routes', 'webhook');

/**
 * Generate webhook signature from JSON string (matching exact decoding)
 */
function generateWebhookSignatureFromJson(string $jsonPayload): string
{
    $secretKey = 'sk_test_XKokBfNWv6FIYuTMg5sLPjhJ';
    $payload = json_decode($jsonPayload, true);
    $fieldKeys = ['id', 'amount', 'currency', 'status', 'created'];

    $fields = [];
    foreach ($fieldKeys as $key) {
        if (isset($payload[$key])) {
            $fields[] = $payload[$key];
        }
    }

    $hashString = implode('', $fields);

    return hash_hmac('sha256', $hashString, $secretKey);
}
