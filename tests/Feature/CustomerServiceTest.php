<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Feature;

use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Psr7\Response;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Services\CustomerService;
use TapPay\Tap\Tests\TestCase;

class CustomerServiceTest extends TestCase
{
    protected CustomerService $customerService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customerService = new CustomerService($this->mockHttpClient());
    }

    #[Test]
    public function it_can_create_a_customer_successfully(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123456',
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => [
                'country_code' => '965',
                'number' => '51234567',
            ],
            'metadata' => [
                'user_id' => '123',
            ],
        ])));

        $customer = $this->customerService->create([
            'first_name' => 'John',
            'last_name' => 'Doe',
            'email' => 'john@example.com',
            'phone' => [
                'country_code' => '965',
                'number' => '51234567',
            ],
        ]);

        $this->assertSame('cus_test_123456', $customer->id());
        $this->assertSame('John', $customer->firstName());
        $this->assertSame('Doe', $customer->lastName());
        $this->assertSame('john@example.com', $customer->email());
        $this->assertIsArray($customer->phone());
        $this->assertSame('965', $customer->phone()['country_code']);
        $this->assertSame('51234567', $customer->phone()['number']);
        $this->assertIsArray($customer->metadata());
        $this->assertArrayHasKey('user_id', $customer->metadata());
    }

    #[Test]
    public function it_can_create_customer_with_minimal_data(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_minimal',
            'first_name' => 'Guest',
        ])));

        $customer = $this->customerService->create([
            'first_name' => 'Guest',
        ]);

        $this->assertSame('cus_test_minimal', $customer->id());
        $this->assertSame('Guest', $customer->firstName());
        $this->assertNull($customer->lastName());
        $this->assertNull($customer->email());
        $this->assertNull($customer->phone());
        $this->assertEmpty($customer->metadata());
    }

    #[Test]
    public function it_can_retrieve_a_customer(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123456',
            'first_name' => 'Jane',
            'last_name' => 'Smith',
            'email' => 'jane@example.com',
        ])));

        $customer = $this->customerService->retrieve('cus_test_123456');

        $this->assertSame('cus_test_123456', $customer->id());
        $this->assertSame('Jane', $customer->firstName());
        $this->assertSame('Smith', $customer->lastName());
        $this->assertSame('jane@example.com', $customer->email());
    }

    #[Test]
    public function it_can_update_a_customer(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123456',
            'first_name' => 'John',
            'last_name' => 'Updated',
            'email' => 'john.updated@example.com',
            'metadata' => [
                'updated' => true,
                'timestamp' => '2024-01-01',
            ],
        ])));

        $customer = $this->customerService->update('cus_test_123456', [
            'last_name' => 'Updated',
            'email' => 'john.updated@example.com',
            'metadata' => [
                'updated' => true,
                'timestamp' => '2024-01-01',
            ],
        ]);

        $this->assertSame('cus_test_123456', $customer->id());
        $this->assertSame('Updated', $customer->lastName());
        $this->assertSame('john.updated@example.com', $customer->email());
        $this->assertArrayHasKey('updated', $customer->metadata());
        $this->assertTrue($customer->metadata()['updated']);
        $this->assertSame('2024-01-01', $customer->metadata()['timestamp']);
    }

    #[Test]
    public function it_can_list_customers(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'customers' => [
                [
                    'id' => 'cus_test_1',
                    'first_name' => 'Alice',
                    'email' => 'alice@example.com',
                ],
                [
                    'id' => 'cus_test_2',
                    'first_name' => 'Bob',
                    'email' => 'bob@example.com',
                ],
                [
                    'id' => 'cus_test_3',
                    'first_name' => 'Charlie',
                    'email' => 'charlie@example.com',
                ],
            ],
        ])));

        $customers = $this->customerService->list(['limit' => 10]);

        $this->assertCount(3, $customers);
        $this->assertSame('cus_test_1', $customers[0]->id());
        $this->assertSame('Alice', $customers[0]->firstName());
        $this->assertSame('cus_test_2', $customers[1]->id());
        $this->assertSame('Bob', $customers[1]->firstName());
        $this->assertSame('cus_test_3', $customers[2]->id());
        $this->assertSame('Charlie', $customers[2]->firstName());
    }

    #[Test]
    public function it_handles_empty_customer_list(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'customers' => [],
        ])));

        $customers = $this->customerService->list([]);

        $this->assertCount(0, $customers);
        $this->assertIsArray($customers);
    }

    #[Test]
    public function it_can_delete_a_customer(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_123456',
            'deleted' => true,
        ])));

        $result = $this->customerService->delete('cus_test_123456');

        $this->assertTrue($result);
    }

    #[Test]
    public function it_handles_customer_with_full_phone_data(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_phone',
            'first_name' => 'Test',
            'phone' => [
                'country_code' => '1',
                'number' => '5551234567',
            ],
        ])));

        $customer = $this->customerService->create([
            'first_name' => 'Test',
            'phone' => [
                'country_code' => '1',
                'number' => '5551234567',
            ],
        ]);

        $phone = $customer->phone();
        $this->assertIsArray($phone);
        $this->assertArrayHasKey('country_code', $phone);
        $this->assertArrayHasKey('number', $phone);
        $this->assertSame('1', $phone['country_code']);
        $this->assertSame('5551234567', $phone['number']);
    }

    #[Test]
    public function it_handles_customer_without_phone(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_no_phone',
            'first_name' => 'Test',
            'email' => 'test@example.com',
        ])));

        $customer = $this->customerService->create([
            'first_name' => 'Test',
            'email' => 'test@example.com',
        ]);

        $this->assertNull($customer->phone());
    }

    #[Test]
    public function it_handles_customer_without_metadata(): void
    {
        $this->mockHandler->append(new Response(200, [], json_encode([
            'id' => 'cus_test_no_metadata',
            'first_name' => 'Test',
        ])));

        $customer = $this->customerService->retrieve('cus_test_no_metadata');

        $this->assertIsArray($customer->metadata());
        $this->assertEmpty($customer->metadata());
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $this->mockHandler->append(new Response(401, [], json_encode([
            'error' => 'Unauthorized',
        ])));

        $this->expectException(AuthenticationException::class);

        $this->customerService->create([
            'first_name' => 'Test',
        ]);
    }

    #[Test]
    public function it_throws_invalid_request_exception_on_422(): void
    {
        $this->mockHandler->append(new Response(422, [], json_encode([
            'message' => 'Invalid customer data',
            'errors' => ['first_name' => ['The first name field is required']],
        ])));

        $this->expectException(InvalidRequestException::class);

        $this->customerService->create([]);
    }

    #[Test]
    public function it_throws_api_error_exception_on_400(): void
    {
        $this->mockHandler->append(new Response(400, [], json_encode([
            'message' => 'Invalid email format',
            'errors' => ['email' => ['The email must be a valid email address']],
        ])));

        try {
            $this->customerService->create([
                'first_name' => 'Test',
                'email' => 'invalid-email',
            ]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Invalid email format', $e->getMessage());
            $this->assertSame(400, $e->getStatusCode());
            $this->assertTrue($e->hasErrors());
            $this->assertSame('The email must be a valid email address', $e->getFirstError());
        }
    }

    #[Test]
    public function it_throws_exception_when_retrieving_invalid_customer_id(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Customer not found',
        ])));

        try {
            $this->customerService->retrieve('invalid_customer_id');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Customer not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_throws_exception_when_updating_non_existent_customer(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Customer not found',
        ])));

        try {
            $this->customerService->update('cus_nonexistent', [
                'email' => 'new@example.com',
            ]);
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Customer not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_throws_exception_when_deleting_non_existent_customer(): void
    {
        $this->mockHandler->append(new Response(404, [], json_encode([
            'message' => 'Customer not found',
        ])));

        try {
            $this->customerService->delete('cus_nonexistent');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Customer not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_handles_server_errors(): void
    {
        $this->mockHandler->append(new Response(500, [], json_encode([
            'message' => 'Internal Server Error',
        ])));

        $this->expectException(ApiErrorException::class);

        $this->customerService->create([
            'first_name' => 'Test',
        ]);
    }
}