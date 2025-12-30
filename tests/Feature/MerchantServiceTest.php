<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use GuzzleHttp\Psr7\Response;
use PHPUnit\Framework\Attributes\Test;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Services\MerchantService;
use TapPay\Tap\Tests\TestCase;

class MerchantServiceTest extends TestCase
{
    protected MerchantService $merchantService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->merchantService = new MerchantService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_create_a_merchant(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'merchant_test_123456',
            'name' => 'Vendor Store',
            'email' => 'vendor@example.com',
            'country_code' => 'SA',
            'type' => 'company',
            'status' => 'ACTIVE',
        ])));

        $merchant = $this->merchantService->create([
            'name' => 'Vendor Store',
            'email' => 'vendor@example.com',
            'country_code' => 'SA',
        ]);

        $this->assertSame('merchant_test_123456', $merchant->id());
        $this->assertSame('Vendor Store', $merchant->name());
        $this->assertSame('vendor@example.com', $merchant->email());
        $this->assertSame('SA', $merchant->countryCode());
        $this->assertSame('company', $merchant->type());
        $this->assertTrue($merchant->isActive());
    }

    #[Test]
    public function it_can_retrieve_a_merchant(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'merchant_test_123456',
            'name' => 'Vendor Store',
            'email' => 'vendor@example.com',
            'status' => 'ACTIVE',
            'verification' => [
                'status' => 'VERIFIED',
            ],
        ])));

        $merchant = $this->merchantService->retrieve('merchant_test_123456');

        $this->assertSame('merchant_test_123456', $merchant->id());
        $this->assertTrue($merchant->isActive());
        $this->assertTrue($merchant->isVerified());
    }

    #[Test]
    public function it_can_update_a_merchant(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'merchant_test_123456',
            'name' => 'Updated Store',
            'email' => 'updated@example.com',
            'status' => 'ACTIVE',
        ])));

        $merchant = $this->merchantService->update('merchant_test_123456', [
            'name' => 'Updated Store',
            'email' => 'updated@example.com',
        ]);

        $this->assertSame('Updated Store', $merchant->name());
        $this->assertSame('updated@example.com', $merchant->email());
    }

    #[Test]
    public function it_can_list_merchants(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'merchants' => [
                [
                    'id' => 'merchant_test_1',
                    'name' => 'Store One',
                    'status' => 'ACTIVE',
                ],
                [
                    'id' => 'merchant_test_2',
                    'name' => 'Store Two',
                    'status' => 'PENDING',
                ],
            ],
        ])));

        $merchants = $this->merchantService->list(['limit' => 10]);

        $this->assertCount(2, $merchants);
        $this->assertSame('merchant_test_1', $merchants[0]->id());
        $this->assertSame('Store One', $merchants[0]->name());
        $this->assertTrue($merchants[0]->isActive());
        $this->assertSame('merchant_test_2', $merchants[1]->id());
        $this->assertFalse($merchants[1]->isActive());
    }

    #[Test]
    public function it_handles_empty_merchant_list(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'merchants' => [],
        ])));

        $merchants = $this->merchantService->list([]);

        $this->assertCount(0, $merchants);
    }

    #[Test]
    public function it_can_delete_a_merchant(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'merchant_test_123456',
            'deleted' => true,
        ])));

        $this->merchantService->delete('merchant_test_123456');

        $this->assertTrue(true);
    }

    #[Test]
    public function it_handles_merchant_with_business_details(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'merchant_test_business',
            'name' => 'Business Store',
            'business' => [
                'name' => 'Business LLC',
                'registration_number' => '123456789',
            ],
            'bank_account' => [
                'iban' => 'SA0380000000608010167519',
                'bank_name' => 'Al Rajhi Bank',
            ],
            'payout_schedule' => 'WEEKLY',
        ])));

        $merchant = $this->merchantService->retrieve('merchant_test_business');

        $this->assertIsArray($merchant->business());
        $this->assertSame('Business LLC', $merchant->business()['name']);
        $this->assertIsArray($merchant->bankAccount());
        $this->assertSame('Al Rajhi Bank', $merchant->bankAccount()['bank_name']);
        $this->assertSame('WEEKLY', $merchant->payoutSchedule());
    }

    #[Test]
    public function it_handles_unverified_merchant(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'merchant_test_unverified',
            'name' => 'Unverified Store',
            'verification' => [
                'status' => 'PENDING',
            ],
        ])));

        $merchant = $this->merchantService->retrieve('merchant_test_unverified');

        $this->assertFalse($merchant->isVerified());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->merchantService->create([
            'name' => 'Test Store',
        ]);
    }

    #[Test]
    public function it_throws_api_error_exception_on_404(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Merchant not found',
        ])));

        try {
            $this->merchantService->retrieve('merchant_nonexistent');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Merchant not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }
}
