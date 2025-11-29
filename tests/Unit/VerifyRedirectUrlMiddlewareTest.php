<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use TapPay\Tap\Http\Middleware\VerifyRedirectUrl;

test('allows request without redirect parameter', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('/callback', 'GET');

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows request with relative redirect URL', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('/callback', 'GET', ['redirect' => '/dashboard']);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows request with same host redirect URL', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://example.com/callback', 'GET', [
        'redirect' => 'https://example.com/dashboard',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows request with same host and different path', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://myapp.com/orders/123/success',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows request with same host and query string', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://myapp.com/dashboard?tab=payments',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('denies request with different host redirect URL', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://malicious.com/steal',
    ]);

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(AccessDeniedHttpException::class, 'Invalid redirect URL.')->group('unit', 'middleware');

test('denies request with subdomain redirect URL', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://evil.myapp.com/steal',
    ]);

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(AccessDeniedHttpException::class, 'Invalid redirect URL.')->group('unit', 'middleware');

test('denies request with similar looking domain', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://myapp.com.attacker.com/steal',
    ]);

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(AccessDeniedHttpException::class, 'Invalid redirect URL.')->group('unit', 'middleware');

test('allows empty redirect parameter', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('/callback', 'GET', ['redirect' => '']);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows redirect with path only', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('/callback', 'GET', [
        'redirect' => '/checkout/complete',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows redirect with hash fragment', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://myapp.com/page#section',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('denies protocol-relative URL with different host', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => '//evil.com/steal',
    ]);

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(AccessDeniedHttpException::class, 'Invalid redirect URL.')->group('unit', 'middleware');

test('allows request with same host different port', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://myapp.com:8080/dashboard',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows request with localhost redirect on localhost', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('http://localhost/callback', 'GET', [
        'redirect' => 'http://localhost/dashboard',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('denies localhost redirect on production domain', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'http://localhost/steal',
    ]);

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(AccessDeniedHttpException::class, 'Invalid redirect URL.')->group('unit', 'middleware');

test('passes request to next middleware', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('/callback', 'GET', ['tap_id' => 'chg_123']);

    $nextCalled = false;
    $middleware->handle($request, function ($req) use (&$nextCalled) {
        $nextCalled = true;
        expect($req->query('tap_id'))->toBe('chg_123');

        return response('OK');
    });

    expect($nextCalled)->toBeTrue();
})->group('unit', 'middleware');

test('blocks javascript protocol URL for XSS protection', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'javascript:alert(1)',
    ]);

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(AccessDeniedHttpException::class, 'Invalid redirect URL.')->group('unit', 'middleware');

test('denies URL with credentials in different host', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://user:pass@evil.com/steal',
    ]);

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(AccessDeniedHttpException::class, 'Invalid redirect URL.')->group('unit', 'middleware');

test('allows URL with credentials in same host', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'https://user:pass@myapp.com/secure',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows malformed URL without valid host', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://myapp.com/callback', 'GET', [
        'redirect' => 'ht tp://invalid url',
    ]);

    // Malformed URLs without a valid host pass through
    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows same host with different case', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('https://MyApp.com/callback', 'GET', [
        'redirect' => 'https://myapp.com/dashboard',
    ]);

    // DNS is case-insensitive, so hosts with different cases are considered the same
    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('allows IP address redirect when request is from same IP', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('http://192.168.1.1/callback', 'GET', [
        'redirect' => 'http://192.168.1.1/dashboard',
    ]);

    $response = $middleware->handle($request, fn ($req) => response('OK'));

    expect($response->getContent())->toBe('OK');
})->group('unit', 'middleware');

test('denies IP address redirect when request is from different IP', function () {
    $middleware = new VerifyRedirectUrl;
    $request = Request::create('http://192.168.1.1/callback', 'GET', [
        'redirect' => 'http://10.0.0.1/steal',
    ]);

    $middleware->handle($request, fn ($req) => response('OK'));
})->throws(AccessDeniedHttpException::class, 'Invalid redirect URL.')->group('unit', 'middleware');
