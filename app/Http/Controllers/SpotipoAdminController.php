<?php

namespace App\Http\Controllers;

use App\Services\Spotipo\SpotipoApiException;
use App\Services\Spotipo\SpotipoProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SpotipoAdminController extends Controller
{
    public function __construct(private readonly SpotipoProvider $spotipo)
    {
    }

    public function index(Request $request): View
    {
        return view('spotipo.index', [
            'configured' => filled(config('services.spotipo.auth_token')) && filled(config('services.spotipo.site_id')),
            'baseUrl' => config('services.spotipo.base_url'),
            'siteId' => config('services.spotipo.site_id'),
            'sites' => $request->session()->pull('spotipo_sites', []),
            'vouchers' => $request->session()->pull('spotipo_vouchers', []),
            'guestUsers' => $request->session()->pull('spotipo_guest_users', []),
            'result' => $request->session()->pull('spotipo_result'),
            'error' => $request->session()->pull('spotipo_error'),
        ]);
    }

    public function test(): RedirectResponse
    {
        return $this->run(fn () => $this->spotipo->sites(), 'spotipo_sites', 'Spotipo sites loaded.');
    }

    public function listVouchers(Request $request): RedirectResponse
    {
        return $this->run(fn () => $this->spotipo->vouchers($this->pagination($request)), 'spotipo_vouchers', 'Spotipo vouchers loaded.');
    }

    public function storeVoucher(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'num_to_create' => ['required', 'integer', 'min:1', 'max:1000'],
            'num_devices' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_type' => ['nullable', 'integer', 'in:1,2,3'],
            'duration_val' => ['nullable', 'integer', 'min:1'],
            'unlimited_speed' => ['nullable', 'boolean'],
            'unlimited_data' => ['nullable', 'boolean'],
            'speed_dl' => ['nullable', 'integer', 'min:0'],
            'speed_ul' => ['nullable', 'integer', 'min:0'],
            'bytes_t' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        return $this->run(fn () => $this->spotipo->createVoucher($this->booleans($payload)), 'spotipo_result', 'Voucher create request sent.');
    }

    public function showVoucher(string $voucherId): RedirectResponse
    {
        return $this->run(fn () => $this->spotipo->voucher($voucherId), 'spotipo_result', 'Voucher loaded.');
    }

    public function updateVoucher(Request $request, string $voucherId): RedirectResponse
    {
        $payload = $request->validate([
            'num_devices' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_type' => ['nullable', 'integer', 'in:1,2,3'],
            'duration_val' => ['nullable', 'integer', 'min:1'],
            'unlimited_speed' => ['nullable', 'boolean'],
            'unlimited_data' => ['nullable', 'boolean'],
            'speed_dl' => ['nullable', 'integer', 'min:0'],
            'speed_ul' => ['nullable', 'integer', 'min:0'],
            'bytes_t' => ['nullable', 'integer', 'min:0'],
            'notes' => ['nullable', 'string', 'max:500'],
            'price' => ['nullable', 'numeric', 'min:0'],
        ]);

        return $this->run(fn () => $this->spotipo->updateVoucher($voucherId, $this->booleans($payload)), 'spotipo_result', 'Voucher update request sent.');
    }

    public function deleteVoucher(string $voucherId): RedirectResponse
    {
        return $this->run(fn () => $this->spotipo->deleteVoucher($voucherId), 'spotipo_result', 'Voucher delete request sent.');
    }

    public function listGuestUsers(Request $request): RedirectResponse
    {
        return $this->run(fn () => $this->spotipo->guestUsers($this->pagination($request)), 'spotipo_guest_users', 'Spotipo guest users loaded.');
    }

    public function storeGuestUser(Request $request): RedirectResponse
    {
        $payload = $request->validate([
            'username' => ['required', 'string', 'max:120'],
            'password' => ['nullable', 'string', 'max:120'],
            'num_devices' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_type' => ['required', 'integer', 'in:1,2,3'],
            'duration_val' => ['required', 'integer', 'min:1'],
            'unlimited_speed' => ['nullable', 'boolean'],
            'unlimited_data' => ['nullable', 'boolean'],
            'speed_dl' => ['nullable', 'integer', 'min:0'],
            'speed_ul' => ['nullable', 'integer', 'min:0'],
            'bytes_t' => ['nullable', 'integer', 'min:0'],
            'bytes_type' => ['nullable', 'integer', 'in:1,2,3,4'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->run(fn () => $this->spotipo->createGuestUser($this->booleans($payload)), 'spotipo_result', 'Guest user create request sent.');
    }

    public function showGuestUser(string $username): RedirectResponse
    {
        return $this->run(fn () => $this->spotipo->guestUser($username), 'spotipo_result', 'Guest user loaded.');
    }

    public function updateGuestUser(Request $request, string $username): RedirectResponse
    {
        $payload = $request->validate([
            'password' => ['nullable', 'string', 'max:120'],
            'num_devices' => ['required', 'integer', 'min:1', 'max:100'],
            'duration_type' => ['required', 'integer', 'in:1,2,3'],
            'duration_val' => ['required', 'integer', 'min:1'],
            'unlimited_speed' => ['nullable', 'boolean'],
            'unlimited_data' => ['nullable', 'boolean'],
            'speed_dl' => ['nullable', 'integer', 'min:0'],
            'speed_ul' => ['nullable', 'integer', 'min:0'],
            'bytes_t' => ['nullable', 'integer', 'min:0'],
            'bytes_type' => ['nullable', 'integer', 'in:1,2,3,4'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        return $this->run(fn () => $this->spotipo->updateGuestUser($username, $this->booleans($payload)), 'spotipo_result', 'Guest user update request sent.');
    }

    public function deleteGuestUser(string $username): RedirectResponse
    {
        return $this->run(fn () => $this->spotipo->deleteGuestUser($username), 'spotipo_result', 'Guest user delete request sent.');
    }

    private function run(callable $operation, string $sessionKey, string $message): RedirectResponse
    {
        try {
            return back()
                ->with($sessionKey, $operation())
                ->with('status', $message);
        } catch (SpotipoApiException $exception) {
            return back()->with('spotipo_error', [
                'message' => $exception->getMessage(),
                'status' => $exception->status,
                'context' => $exception->context,
            ]);
        }
    }

    private function pagination(Request $request): array
    {
        return array_filter($request->validate([
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
            'search' => ['nullable', 'string', 'max:120'],
        ]), fn ($value) => $value !== null && $value !== '');
    }

    private function booleans(array $payload): array
    {
        foreach (['unlimited_speed', 'unlimited_data'] as $key) {
            $payload[$key] = (bool) ($payload[$key] ?? false);
        }

        return array_filter($payload, fn ($value) => $value !== null && $value !== '');
    }
}
