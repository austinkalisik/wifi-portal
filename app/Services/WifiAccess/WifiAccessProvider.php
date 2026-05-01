<?php

namespace App\Services\WifiAccess;

interface WifiAccessProvider
{
    public function name(): string;

    /**
     * @return array<int, array<string, mixed>>
     */
    public function sites(): array;

    /**
     * @return array<string, mixed>
     */
    public function vouchers(array $query = []): array;

    /**
     * @return array<string, mixed>
     */
    public function createVoucher(array $payload): array;

    /**
     * @return array<string, mixed>
     */
    public function voucher(int|string $voucherId): array;

    /**
     * @return array<string, mixed>
     */
    public function updateVoucher(int|string $voucherId, array $payload): array;

    /**
     * @return array<string, mixed>
     */
    public function deleteVoucher(int|string $voucherId): array;

    /**
     * @return array<string, mixed>
     */
    public function guestUsers(array $query = []): array;

    /**
     * @return array<string, mixed>
     */
    public function createGuestUser(array $payload): array;

    /**
     * @return array<string, mixed>
     */
    public function guestUser(string $username): array;

    /**
     * @return array<string, mixed>
     */
    public function updateGuestUser(string $username, array $payload): array;

    /**
     * @return array<string, mixed>
     */
    public function deleteGuestUser(string $username): array;
}
