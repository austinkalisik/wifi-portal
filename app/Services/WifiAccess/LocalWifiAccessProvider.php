<?php

namespace App\Services\WifiAccess;

use App\Models\Business;
use App\Models\GuestSession;
use App\Models\WifiPackage;

class LocalWifiAccessProvider implements WifiAccessProvider
{
    public function name(): string
    {
        return 'local';
    }

    public function sites(): array
    {
        return Business::query()
            ->get(['id', 'site_name'])
            ->map(fn (Business $business): array => [
                'id' => $business->id,
                'name' => $business->site_name,
            ])
            ->values()
            ->all();
    }

    public function vouchers(array $query = []): array
    {
        return [
            'items' => WifiPackage::query()->where('access_type', 'subscription')->latest()->get()->all(),
        ];
    }

    public function createVoucher(array $payload): array
    {
        $business = Business::query()->firstOrFail();

        $package = $business->packages()->create([
            'name' => $payload['notes'] ?? 'Local Voucher',
            'access_type' => 'subscription',
            'description' => $payload['notes'] ?? null,
            'duration_minutes' => (int) ($payload['duration_val'] ?? 60),
            'ad_watch_seconds' => null,
            'price' => (float) ($payload['price'] ?? 0),
            'download_mbps' => (int) ($payload['speed_dl'] ?? 10),
            'upload_mbps' => (int) ($payload['speed_ul'] ?? 5),
            'is_active' => true,
        ]);

        return $package->toArray();
    }

    public function voucher(int|string $voucherId): array
    {
        return WifiPackage::query()->findOrFail($voucherId)->toArray();
    }

    public function updateVoucher(int|string $voucherId, array $payload): array
    {
        $package = WifiPackage::query()->findOrFail($voucherId);
        $package->update([
            'description' => $payload['notes'] ?? $package->description,
            'price' => $payload['price'] ?? $package->price,
            'download_mbps' => $payload['speed_dl'] ?? $package->download_mbps,
            'upload_mbps' => $payload['speed_ul'] ?? $package->upload_mbps,
        ]);

        return $package->fresh()->toArray();
    }

    public function deleteVoucher(int|string $voucherId): array
    {
        WifiPackage::query()->findOrFail($voucherId)->delete();

        return ['ok' => true];
    }

    public function guestUsers(array $query = []): array
    {
        return [
            'items' => GuestSession::query()->latest()->limit((int) ($query['per_page'] ?? 20))->get()->all(),
        ];
    }

    public function createGuestUser(array $payload): array
    {
        $business = Business::query()->firstOrFail();
        $session = $business->sessions()->create([
            'access_method' => 'subscription',
            'guest_name' => $payload['name'] ?? $payload['username'],
            'device_mac' => $payload['username'],
            'status' => 'active',
            'started_at' => now(),
            'expires_at' => now()->addMinutes((int) ($payload['duration_val'] ?? 60)),
        ]);

        return $session->toArray();
    }

    public function guestUser(string $username): array
    {
        return GuestSession::query()->where('device_mac', $username)->latest()->firstOrFail()->toArray();
    }

    public function updateGuestUser(string $username, array $payload): array
    {
        $session = GuestSession::query()->where('device_mac', $username)->latest()->firstOrFail();
        $session->update([
            'guest_name' => $payload['name'] ?? $session->guest_name,
            'status' => $payload['status'] ?? $session->status,
        ]);

        return $session->fresh()->toArray();
    }

    public function deleteGuestUser(string $username): array
    {
        GuestSession::query()->where('device_mac', $username)->delete();

        return ['ok' => true];
    }
}
