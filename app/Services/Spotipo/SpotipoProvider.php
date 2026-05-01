<?php

namespace App\Services\Spotipo;

use App\Services\WifiAccess\WifiAccessProvider;

class SpotipoProvider implements WifiAccessProvider
{
    public function __construct(
        private readonly SpotipoClient $client,
        private readonly ?string $siteId,
    ) {
    }

    public function name(): string
    {
        return 'spotipo';
    }

    public function sites(): array
    {
        return $this->client->get('/ext/api/v1/sites/');
    }

    public function vouchers(array $query = []): array
    {
        return $this->client->get($this->sitePath('/api/v1/voucher/'), $query);
    }

    public function createVoucher(array $payload): array
    {
        return $this->client->post($this->sitePath('/api/v1/voucher/'), $payload);
    }

    public function voucher(int|string $voucherId): array
    {
        return $this->client->get($this->sitePath("/api/v1/voucher/$voucherId"));
    }

    public function updateVoucher(int|string $voucherId, array $payload): array
    {
        return $this->client->put($this->sitePath("/api/v1/voucher/$voucherId"), $payload);
    }

    public function deleteVoucher(int|string $voucherId): array
    {
        return $this->client->delete($this->sitePath("/api/v1/voucher/$voucherId"));
    }

    public function guestUsers(array $query = []): array
    {
        return $this->client->get($this->sitePath('/api/v1/guestuser/'), $query);
    }

    public function createGuestUser(array $payload): array
    {
        return $this->client->post($this->sitePath('/api/v1/guestuser/'), $payload);
    }

    public function guestUser(string $username): array
    {
        return $this->client->get($this->sitePath('/api/v1/guestuser/u/'.rawurlencode($username)));
    }

    public function updateGuestUser(string $username, array $payload): array
    {
        return $this->client->put($this->sitePath('/api/v1/guestuser/u/'.rawurlencode($username)), $payload);
    }

    public function deleteGuestUser(string $username): array
    {
        return $this->client->delete($this->sitePath('/api/v1/guestuser/u/'.rawurlencode($username)));
    }

    private function sitePath(string $path): string
    {
        if (! filled($this->siteId)) {
            throw new SpotipoApiException('Spotipo site ID is not configured. Set SPOTIPO_SITE_ID.');
        }

        return '/ext/'.$this->siteId.'/'.ltrim($path, '/');
    }
}
