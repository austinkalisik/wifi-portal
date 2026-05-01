<?php

namespace App\Services\Spotipo;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

class SpotipoClient
{
    public function __construct(
        private readonly string $baseUrl,
        private readonly ?string $authToken,
        private readonly int $timeout = 15,
    ) {
    }

    public function configured(): bool
    {
        return filled($this->baseUrl) && filled($this->authToken);
    }

    public function get(string $path, array $query = []): array
    {
        return $this->request('get', $path, $query);
    }

    public function post(string $path, array $payload = []): array
    {
        return $this->request('post', $path, $payload);
    }

    public function put(string $path, array $payload = []): array
    {
        return $this->request('put', $path, $payload);
    }

    public function delete(string $path): array
    {
        return $this->request('delete', $path);
    }

    private function request(string $method, string $path, array $data = []): array
    {
        if (! $this->configured()) {
            throw new SpotipoApiException('Spotipo is not configured. Set SPOTIPO_AUTH_TOKEN and SPOTIPO_BASE_URL.');
        }

        $url = rtrim($this->baseUrl, '/').'/'.ltrim($path, '/');

        try {
            $pending = Http::acceptJson()
                ->asJson()
                ->timeout($this->timeout)
                ->withHeaders(['Authentication-Token' => $this->authToken]);

            /** @var Response $response */
            $response = match ($method) {
                'get' => $pending->get($url, $data),
                'post' => $pending->post($url, $data),
                'put' => $pending->put($url, $data),
                'delete' => $pending->delete($url),
                default => throw new SpotipoApiException("Unsupported Spotipo method [$method]."),
            };
        } catch (ConnectionException $exception) {
            throw new SpotipoApiException('Could not connect to Spotipo.', 0, [
                'error' => $exception->getMessage(),
            ]);
        }

        if ($response->failed()) {
            throw new SpotipoApiException(
                'Spotipo request failed.',
                $response->status(),
                ['body' => $response->json() ?? $response->body()]
            );
        }

        $json = $response->json();

        if (is_array($json)) {
            return $json;
        }

        return ['ok' => true, 'body' => $response->body()];
    }
}
