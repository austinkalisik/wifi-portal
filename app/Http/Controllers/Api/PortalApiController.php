<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Business;
use App\Models\GuestSession;
use App\Models\Router;
use App\Models\WifiPackage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;

class PortalApiController extends Controller
{
    public function config(Request $request): JsonResponse
    {
        $business = $this->businessFromRequest($request);
        abort_unless($business, 401, 'Invalid API key.');

        $business->load(['brandingProfile', 'packages' => fn ($query) => $query->where('is_active', true)]);

        return response()->json([
            'business' => [
                'company_name' => $business->company_name,
                'site_name' => $business->site_name,
                'currency' => $business->currency,
                'timezone' => $business->timezone,
            ],
            'branding' => $business->brandingProfile,
            'packages' => $business->packages->values(),
            'guest_access_url' => url('/guest'),
        ]);
    }

    public function heartbeat(Request $request): JsonResponse
    {
        $business = $this->businessFromRequest($request);
        abort_unless($business, 401, 'Invalid API key.');

        $data = $request->validate([
            'shared_secret' => ['required', 'string'],
            'ip_address' => ['nullable', 'ip'],
            'status' => ['nullable', 'in:online,offline,degraded'],
        ]);

        $router = $business->routers()->where('shared_secret', $data['shared_secret'])->firstOrFail();
        $router->update([
            'ip_address' => $data['ip_address'] ?? $router->ip_address,
            'status' => $data['status'] ?? 'online',
            'last_seen_at' => now(),
        ]);

        return response()->json([
            'ok' => true,
            'router' => [
                'id' => $router->id,
                'name' => $router->name,
                'status' => $router->status,
                'last_seen_at' => $router->last_seen_at,
            ],
        ]);
    }

    public function createSession(Request $request): JsonResponse
    {
        $business = $this->businessFromRequest($request);
        abort_unless($business, 401, 'Invalid API key.');

        $data = $request->validate([
            'router_id' => ['nullable', 'exists:routers,id'],
            'package_id' => ['nullable', 'exists:wifi_packages,id'],
            'guest_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
            'device_mac' => ['required', 'string', 'max:32'],
        ]);

        $package = isset($data['package_id'])
            ? $business->packages()->whereKey($data['package_id'])->first()
            : $business->packages()->where('is_active', true)->orderBy('price')->first();

        $startsAt = now();
        $expiresAt = $package
            ? Carbon::instance($startsAt)->addMinutes($package->duration_minutes)
            : null;

        $session = GuestSession::create([
            'business_id' => $business->id,
            'router_id' => $data['router_id'] ?? null,
            'wifi_package_id' => $package?->id,
            'access_method' => $package?->access_type ?? 'subscription',
            'guest_name' => $data['guest_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'payment_reference' => $data['payment_reference'] ?? null,
            'amount_paid' => $package?->price ?? 0,
            'device_mac' => $data['device_mac'],
            'status' => 'active',
            'started_at' => $startsAt,
            'expires_at' => $expiresAt,
        ]);

        return response()->json([
            'ok' => true,
            'session_id' => $session->id,
            'package' => $package?->name,
            'expires_at' => $session->expires_at,
        ], 201);
    }

    public function adAccess(Request $request): JsonResponse
    {
        $business = $this->businessFromRequest($request);
        abort_unless($business, 401, 'Invalid API key.');

        $data = $request->validate([
            'guest_name' => ['nullable', 'string', 'max:120'],
            'device_mac' => ['required', 'string', 'max:32'],
            'ad_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $package = $business->packages()
            ->where('access_type', 'ad')
            ->where('is_active', true)
            ->firstOrFail();

        $session = $this->grantAccess($business, $package, $data, 'ad');

        return response()->json([
            'ok' => true,
            'access_method' => 'ad',
            'session_id' => $session->id,
            'expires_at' => $session->expires_at,
        ], 201);
    }

    public function subscriptionAccess(Request $request): JsonResponse
    {
        $business = $this->businessFromRequest($request);
        abort_unless($business, 401, 'Invalid API key.');

        $data = $request->validate([
            'package_id' => ['required', 'exists:wifi_packages,id'],
            'guest_name' => ['nullable', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'device_mac' => ['required', 'string', 'max:32'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
        ]);

        $package = $business->packages()
            ->whereKey($data['package_id'])
            ->where('access_type', 'subscription')
            ->where('is_active', true)
            ->firstOrFail();

        $session = $this->grantAccess($business, $package, $data, 'subscription');

        return response()->json([
            'ok' => true,
            'access_method' => 'subscription',
            'session_id' => $session->id,
            'amount_paid' => $session->amount_paid,
            'expires_at' => $session->expires_at,
        ], 201);
    }

    public function routers(Request $request): JsonResponse
    {
        $business = $this->businessFromRequest($request);
        abort_unless($business, 401, 'Invalid API key.');

        return response()->json([
            'routers' => $business->routers()->latest()->get(),
        ]);
    }

    private function businessFromRequest(Request $request): ?Business
    {
        $key = $request->header('X-Portal-Key') ?: $request->query('api_key');

        return $key ? Business::where('api_key', $key)->first() : null;
    }

    private function grantAccess(Business $business, WifiPackage $package, array $data, string $method): GuestSession
    {
        $startsAt = now();

        return GuestSession::create([
            'business_id' => $business->id,
            'wifi_package_id' => $package->id,
            'access_method' => $method,
            'guest_name' => $data['guest_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'payment_reference' => $data['payment_reference'] ?? $data['ad_reference'] ?? ($method === 'subscription' ? 'manual-'.Str::upper(Str::random(8)) : null),
            'amount_paid' => $method === 'subscription' ? $package->price : 0,
            'device_mac' => $data['device_mac'],
            'status' => 'active',
            'started_at' => $startsAt,
            'expires_at' => Carbon::instance($startsAt)->addMinutes($package->duration_minutes),
        ]);
    }
}
