<?php

declare(strict_types=1);

namespace TapPay\Tap\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\ServerException;
use TapPay\Tap\Exceptions\ApiErrorException;
use TapPay\Tap\Exceptions\AuthenticationException;
use TapPay\Tap\Exceptions\InvalidRequestException;

class Client
{
    protected GuzzleClient $client;
    protected string $secretKey;
    protected string $baseUrl;

    public function __construct(string $secretKey, ?string $baseUrl = null)
    {
        $this->secretKey = $secretKey;
        $this->baseUrl = $baseUrl ?? config('tap.base_url', 'https://api.tap.company/v2/');

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
     */
    public function get(string $endpoint, array $query = []): array
    {
        return $this->request('GET', $endpoint, ['query' => $query]);
    }

    /**
     * Make a POST request
     */
    public function post(string $endpoint, array $data = []): array
    {
        return $this->request('POST', $endpoint, ['json' => $data]);
    }

    /**
     * Make a PUT request
     */
    public function put(string $endpoint, array $data = []): array
    {
        return $this->request('PUT', $endpoint, ['json' => $data]);
    }

    /**
     * Make a DELETE request
     */
    public function delete(string $endpoint): array
    {
        return $this->request('DELETE', $endpoint);
    }

    /**
     * Make an HTTP request
     */
    protected function request(string $method, string $endpoint, array $options = []): array
    {
        try {
            $response = $this->client->request($method, $endpoint, $options);
            $body = (string) $response->getBody();

            return json_decode($body, true) ?? [];
        } catch (ClientException $e) {
            $this->handleClientException($e);
        } catch (ServerException $e) {
            $this->handleServerException($e);
        } catch (\Exception $e) {
            throw new ApiErrorException(
                'Network error: ' . $e->getMessage(),
                0
            );
        }
    }

    /**
     * Handle 4xx client errors
     */
    protected function handleClientException(ClientException $e): void
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = (string) $e->getResponse()->getBody();
        $response = json_decode($body, true) ?? [];

        if ($statusCode === 401) {
            throw new AuthenticationException();
        }

        if ($statusCode === 400 || $statusCode === 422) {
            throw InvalidRequestException::fromResponse($response, $statusCode);
        }

        throw ApiErrorException::fromResponse($response, $statusCode);
    }

    /**
     * Handle 5xx server errors
     */
    protected function handleServerException(ServerException $e): void
    {
        $statusCode = $e->getResponse()->getStatusCode();
        $body = (string) $e->getResponse()->getBody();
        $response = json_decode($body, true) ?? [];

        throw ApiErrorException::fromResponse($response, $statusCode);
    }
}
