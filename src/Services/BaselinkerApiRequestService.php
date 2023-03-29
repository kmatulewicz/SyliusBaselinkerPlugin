<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Services;

use Exception;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BaselinkerApiRequestService implements BaselinkerApiRequestServiceInterface
{
    private HttpClientInterface $client;

    private string $token;

    private string $url;

    private string $method;

    public function __construct(HttpClientInterface $client, string $token, string $url, string $method)
    {
        $this->client = $client;
        $this->token = $token;
        $this->url = $url;
        $this->method = $method;
    }

    public function do(string $method, array $parameters = []): array
    {
        $response = $this->request($method, $parameters);
        $content = $this->getContent($response);

        return $content;
    }

    private function request(string $method, array $parameters = []): ResponseInterface
    {
        $json = json_encode($parameters);

        $body = [
            'method' => $method,
            'parameters' => $json,
        ];

        $query = http_build_query($body);

        $options = [
            'headers' => $this->prepareHeaders(),
            'body' => $query,
        ];

        $response = $this->client->request(
            $this->method,
            $this->url,
            $options,
        );

        return $response;
    }

    private function getContent(ResponseInterface $response): array
    {
        $content = $response->toArray();
        if (!array_key_exists('status', $content)) {
            throw new Exception('Wrong response');
        }
        if ('SUCCESS' != $content['status']) {
            $message = '';
            if (array_key_exists('error_message', $content)) {
                $message = 'Wrong response status: ' . (string) $content['status'] . ' error message: ' . (string) $content['error_message'];
            } else {
                $message = 'Wrong response status: ' . (string) $content['status'] . ' no error message';
            }

            throw new Exception($message);
        }

        return $content;
    }

    private function prepareHeaders(): array
    {
        $headers = [
            'X-BLToken' => $this->token,
        ];

        return $headers;
    }
}
