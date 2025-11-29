<?php

declare(strict_types=1);

namespace TapPay\Tap\Http;

use const JSON_THROW_ON_ERROR;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use InvalidArgumentException;
use JsonException;
use Psr\Http\Message\ResponseInterface;
use SensitiveParameter;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

use function config;
use function json_decode;
use function trim;

/**
 * HTTP client for making requests to Tap Payments API
 */
class Client
{
    protected GuzzleClient $client;

    /**
     * Create a new HTTP client
     *
     * @param  string  $secretKey  Tap API secret key
     * @param  string|null  $baseUrl  Optional custom base URL
     *
     * @throws InvalidArgumentException
     */
    public function __construct(
        #[SensitiveParameter]
        protected string $secretKey,
        protected ?string $baseUrl = null
    ) {
        if (trim($this->secretKey) === '') {
            throw new InvalidArgumentException('Secret key cannot be empty');
        }

        if ($this->baseUrl === null || trim($this->baseUrl) === '') {
            $this->baseUrl = config('tap.base_url', 'https://api.tap.company/v2/');
        }

        $this->client = new GuzzleClient([
            'base_uri' => $this->baseUrl,
            'timeout' => config('tap.timeout', 30),
            'connect_timeout' => config('tap.connect_timeout', 10),
            'headers' => [
                'Authorization' => 'Bearer ' . $this->secretKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);
    }

    /**
     * Make a GET request
     *
     * @param  string  $endpoint  API endpoint
     * @param  array  $query  Query parameters
     * @return array Response data
     *
     * @throws ApiErrorException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request
     *
     * @param  string  $endpoint  API endpoint
     * @param  array  $data  Request body data
     * @return array Response data
     *
     * @throws ApiErrorException
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request
     *
     * @param  string  $endpoint  API endpoint
     * @param  array  $data  Request body data
     * @return array Response data
     *
     * @throws ApiErrorException
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request
     *
     * @param  string  $endpoint  API endpoint
     * @return array Response data
     *
     * @throws ApiErrorException
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make an HTTP request
     *
     * @param  string  $method  HTTP method
     * @param  string  $endpoint  API endpoint
     * @param  array  $options  Guzzle request options
     * @return array Response data
     *
     * @throws ApiErrorException
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);

            return $this->decodeResponse($response);
        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $this->handleServerException($e);
        } catch (GuzzleException $e) {
            throw new ApiErrorException(
                'Network error: ' . $e->getMessage(),
                0
            );
        }
    }

    /**
     * @throws ApiErrorException
     */
    protected function decodeResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return [];
        }

        try {
            return json_decode($body, true, 512, JSON_THROW_ON_ERROR) ?? [];
        } catch (JsonException $e) {
            throw new ApiErrorException('Invalid JSON response: ' . $e->getMessage(), 0);
        }
    }

    /**
     * Extract error details from response
     *
     * @param  array<string, mixed>  $response
     * @return array{message: string, errors: array<mixed>}
     */
    protected function extractErrorDetails(array $response): array
    {
        return [
            'message' => $response['message'] ?? $response['error'] ?? 'Unknown API error',
            'errors' => $response['errors'] ?? [],
        ];
    }

    /**
     * Handle 4xx client errors
     *
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    protected function handleClientException(ClientException $e): never
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $response = $this->decodeResponse($e->getResponse());
        ['message' => $message, 'errors' => $errors] = $this->extractErrorDetails($response);

        throw match ($statusCode) {
            401 => new AuthenticationException,
            400, 422 => new InvalidRequestException($message, $statusCode, $errors),
            default => new ApiErrorException($message, $statusCode, $errors),
        };
    }

    /**
     * Handle 5xx server errors
     *
     * @throws ApiErrorException
     */
    protected function handleServerException(ServerException $e): never
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $response = $this->decodeResponse($e->getResponse());
        ['message' => $message, 'errors' => $errors] = $this->extractErrorDetails($response);

        throw new ApiErrorException($message, $statusCode, $errors);
    }
}
