<?php

declare(strict_types=1);

namespace TapPay\Tap\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\ServerException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use InvalidArgumentException;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;
use TapPay\Tap\Http\Client;
use TapPay\Tap\Tests\TestCase;

class HttpClientTest extends TestCase
{
    #[Test]
    public function it_throws_exception_when_secret_key_is_empty(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Secret key cannot be empty');

        new Client('');
    }

    #[Test]
    public function it_can_create_client_with_valid_secret_key(): void
    {
        $client = new Client('sk_test_XKokBfNWv6FIYuTMg5sLPjhJ');

        $this->assertInstanceOf(Client::class, $client);
    }

    #[Test]
    public function it_can_make_successful_get_request(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['id' => 'test_123', 'status' => 'success'])),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $response = $client->get('charges/test_123');

        $this->assertIsArray($response);
        $this->assertSame('test_123', $response['id']);
        $this->assertSame('success', $response['status']);
    }

    #[Test]
    public function it_can_make_successful_get_request_with_query_parameters(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['charges' => []])),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $response = $client->get('charges', ['limit' => 10, 'page' => 1]);

        $this->assertIsArray($response);
        $this->assertArrayHasKey('charges', $response);
    }

    #[Test]
    public function it_can_make_successful_post_request(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['id' => 'chg_created', 'amount' => 10.50])),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $response = $client->post('charges', [
            'amount' => 10.50,
            'currency' => 'USD',
        ]);

        $this->assertIsArray($response);
        $this->assertSame('chg_created', $response['id']);
        $this->assertSame(10.50, $response['amount']);
    }

    #[Test]
    public function it_can_make_successful_put_request(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['id' => 'chg_updated', 'status' => 'CAPTURED'])),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $response = $client->put('charges/chg_updated', [
            'status' => 'CAPTURED',
        ]);

        $this->assertIsArray($response);
        $this->assertSame('chg_updated', $response['id']);
        $this->assertSame('CAPTURED', $response['status']);
    }

    #[Test]
    public function it_can_make_successful_delete_request(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], json_encode(['id' => 'cus_deleted', 'deleted' => true])),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $response = $client->delete('customers/cus_deleted');

        $this->assertIsArray($response);
        $this->assertSame('cus_deleted', $response['id']);
        $this->assertTrue($response['deleted']);
    }

    #[Test]
    public function it_handles_empty_json_response(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], ''),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $response = $client->get('charges/test_123');

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    #[Test]
    public function it_handles_null_json_response(): void
    {
        $mockHandler = new MockHandler([
            new Response(200, [], 'null'),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $response = $client->get('charges/test_123');

        $this->assertIsArray($response);
        $this->assertEmpty($response);
    }

    #[Test]
    public function it_throws_authentication_exception_on_401(): void
    {
        $mockHandler = new MockHandler([
            new ClientException(
                'Unauthorized',
                new Request('GET', 'charges/test'),
                new Response(401, [], json_encode(['error' => 'Unauthorized']))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $this->expectException(AuthenticationException::class);

        $client->get('charges/test');
    }

    #[Test]
    public function it_throws_invalid_request_exception_on_400(): void
    {
        $mockHandler = new MockHandler([
            new ClientException(
                'Bad Request',
                new Request('POST', 'charges'),
                new Response(400, [], json_encode([
                    'message' => 'Invalid amount',
                    'errors' => ['amount' => ['The amount must be greater than 0.1']],
                ]))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Invalid amount');

        $client->post('charges', ['amount' => 0.01]);
    }

    #[Test]
    public function it_throws_invalid_request_exception_on_422(): void
    {
        $mockHandler = new MockHandler([
            new ClientException(
                'Unprocessable Entity',
                new Request('POST', 'charges'),
                new Response(422, [], json_encode([
                    'message' => 'Validation failed',
                    'errors' => ['currency' => ['The currency field is required']],
                ]))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $this->expectException(InvalidRequestException::class);
        $this->expectExceptionMessage('Validation failed');

        $client->post('charges', ['amount' => 10.50]);
    }

    #[Test]
    public function it_throws_api_error_exception_on_other_4xx_errors(): void
    {
        $mockHandler = new MockHandler([
            new ClientException(
                'Not Found',
                new Request('GET', 'charges/invalid'),
                new Response(404, [], json_encode([
                    'message' => 'Charge not found',
                ]))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        try {
            $client->get('charges/invalid');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertSame('Charge not found', $e->getMessage());
            $this->assertSame(404, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_throws_api_error_exception_on_500(): void
    {
        $mockHandler = new MockHandler([
            new ServerException(
                'Internal Server Error',
                new Request('POST', 'charges'),
                new Response(500, [], json_encode([
                    'message' => 'Internal server error',
                ]))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Internal server error');

        $client->post('charges', ['amount' => 10.50]);
    }

    #[Test]
    public function it_throws_api_error_exception_on_503(): void
    {
        $mockHandler = new MockHandler([
            new ServerException(
                'Service Unavailable',
                new Request('GET', 'charges'),
                new Response(503, [], json_encode([
                    'message' => 'Service temporarily unavailable',
                ]))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        $this->expectException(ApiErrorException::class);
        $this->expectExceptionMessage('Service temporarily unavailable');

        $client->get('charges');
    }

    #[Test]
    public function it_throws_api_error_exception_on_network_error(): void
    {
        $mockHandler = new MockHandler([
            new ConnectException(
                'Connection timeout',
                new Request('GET', 'charges')
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        try {
            $client->get('charges');
            $this->fail('Should have thrown ApiErrorException');
        } catch (ApiErrorException $e) {
            $this->assertStringContainsString('Network error', $e->getMessage());
            $this->assertStringContainsString('Connection timeout', $e->getMessage());
            $this->assertSame(0, $e->getStatusCode());
        }
    }

    #[Test]
    public function it_handles_error_response_with_error_key(): void
    {
        $mockHandler = new MockHandler([
            new ClientException(
                'Bad Request',
                new Request('POST', 'charges'),
                new Response(400, [], json_encode([
                    'error' => 'Invalid parameters',
                ]))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        try {
            $client->post('charges', []);
            $this->fail('Should have thrown InvalidRequestException');
        } catch (InvalidRequestException $e) {
            $this->assertSame('Invalid parameters', $e->getMessage());
        }
    }

    #[Test]
    public function it_handles_error_response_without_message_or_error(): void
    {
        $mockHandler = new MockHandler([
            new ClientException(
                'Bad Request',
                new Request('POST', 'charges'),
                new Response(400, [], json_encode([]))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        try {
            $client->post('charges', []);
            $this->fail('Should have thrown InvalidRequestException');
        } catch (InvalidRequestException $e) {
            $this->assertSame('Unknown API error', $e->getMessage());
        }
    }

    #[Test]
    public function it_handles_error_response_without_errors_field(): void
    {
        $mockHandler = new MockHandler([
            new ClientException(
                'Bad Request',
                new Request('POST', 'charges'),
                new Response(400, [], json_encode([
                    'message' => 'Bad request',
                ]))
            ),
        ]);

        $client = $this->createClientWithMockHandler($mockHandler);

        try {
            $client->post('charges', []);
            $this->fail('Should have thrown InvalidRequestException');
        } catch (InvalidRequestException $e) {
            $this->assertSame('Bad request', $e->getMessage());
            $this->assertFalse($e->hasErrors());
            $this->assertEmpty($e->getErrors());
        }
    }

    /**
     * Helper method to create a Client instance with a mocked Guzzle handler
     */
    protected function createClientWithMockHandler(MockHandler $mockHandler): Client
    {
        $handlerStack = HandlerStack::create($mockHandler);
        $guzzleClient = new GuzzleClient(['handler' => $handlerStack]);

        $client = new Client('sk_test_XKokBfNWv6FIYuTMg5sLPjhJ');

        // Use reflection to inject mocked Guzzle client
        $reflection = new \ReflectionClass($client);
        $property = $reflection->getProperty('client');
        $property->setAccessible(true);
        $property->setValue($client, $guzzleClient);

        return $client;
    }
}