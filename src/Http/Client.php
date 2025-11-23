<?php

declare(strict_types=1);

namespace TapPay\Tap\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\ServerException;
use InvalidArgumentException;
use Psr\Http\Message\ResponseInterface;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

/**
 * HTTP client for making requests to Tap Payments API
 */
class Client
{
    protected GuzzleClient $client;

    /**
     * Create a new HTTP client
     *
     * @param string $secretKey Tap API secret key
     * @param string|null $baseUrl Optional custom base URL
     * @throws InvalidArgumentException
     */
    public function __construct(
        protected string $secretKey,
        protected ?string $baseUrl = null
    ) {
        if (empty($this->secretKey)) {
            throw new InvalidArgumentException('Secret key cannot be empty');
        }

        $this->baseUrl ??= config('tap.base_url', 'https://api.tap.company/v2/');

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
     * @param string $endpoint API endpoint
     * @param array $query Query parameters
     * @return array Response data
     * @throws ApiErrorException
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request
     *
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return array Response data
     * @throws ApiErrorException
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request
     *
     * @param string $endpoint API endpoint
     * @param array $data Request body data
     * @return array Response data
     * @throws ApiErrorException
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request
     *
     * @param string $endpoint API endpoint
     * @return array Response data
     * @throws ApiErrorException
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make an HTTP request
     *
     * @param string $method HTTP method
     * @param string $endpoint API endpoint
     * @param array $options Guzzle request options
     * @return array Response data
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
     * Decode JSON response body
     *
     * @param ResponseInterface $response
     * @return array
     */
    protected function decodeResponse(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();
        return json_decode($body, true) ?? [];
    }

    /**
     * Parse error response
     *
     * @param ResponseInterface $response
     * @return array
     */
    protected function parseErrorResponse(ResponseInterface $response): array
    {
        return $this->decodeResponse($response);
    }

    /**
     * Handle 4xx client errors
     *
     * @param ClientException $e
     * @return never
     * @throws AuthenticationException
     * @throws InvalidRequestException
     * @throws ApiErrorException
     */
    protected function handleClientException(ClientException $e): never
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $response = $this->parseErrorResponse($e->getResponse());

        throw match($statusCode) {
            401 => new AuthenticationException(),
            400, 422 => InvalidRequestException::fromResponse($response, $statusCode),
            default => ApiErrorException::fromResponse($response, $statusCode),
        };
    }

    /**
     * Handle 5xx server errors
     *
     * @param ServerException $e
     * @return never
     * @throws ApiErrorException
     */
    protected function handleServerException(ServerException $e): never
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $response = $this->parseErrorResponse($e->getResponse());

        throw ApiErrorException::fromResponse($response, $statusCode);
    }
}
