<?php

declare(strict_types=1);

namespace SyliusBaselinkerPlugin\Services;

use Symfony\Contracts\HttpClient\HttpClientInterface;
use Symfony\Contracts\HttpClient\ResponseInterface;

class BaselinkerApiService
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

    public function getLastLogId(): ?int
    {
        $parameters = [
            'logs_types' => [1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20],
        ];

        $response = $this->request('getJournalList', $parameters);

        if ($response->getStatusCode() === 200) {
            $content = (array) json_decode($response->getContent(), true);
            if (array_key_exists('status', $content) && $content['status'] === 'SUCCESS') {
                if (array_key_exists('logs', $content)) {
                    $logs = (array) $content['logs'];
                    $last = (array) end($logs);
                    if (array_key_exists('log_id', $last)) {
                        $id = (int) $last['log_id'];

                        return $id;
                    }
                }
            }
        }

        return null;
    }

    private function prepareHeaders(): array
    {
        $headers = [
            'X-BLToken' => $this->token,
        ];

        return $headers;
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
}
