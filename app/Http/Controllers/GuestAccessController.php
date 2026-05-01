<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\GuestSession;
use App\Models\WifiPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GuestAccessController extends Controller
{
    public function show(): View
    {
        $business = $this->business();
        $business->load([
            'brandingProfile',
            'packages' => fn ($query) => $query->where('is_active', true)->orderBy('price'),
        ]);

        return view('guest', [
            'business' => $business,
            'branding' => $business->brandingProfile,
            'adPackage' => $business->packages->firstWhere('access_type', 'ad'),
            'subscriptionPackages' => $business->packages->where('access_type', 'subscription')->values(),
            'guest' => session('guest_profile'),
        ]);
    }

    public function login(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'guest_name' => ['required', 'string', 'max:120'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:160'],
            'device_mac' => ['required', 'string', 'max:32'],
        ]);

        $request->session()->put('guest_profile', $data);

        return redirect()->route('guest.show')->with('guest_status', 'You are signed in. Choose how you want to connect.');
    }

    public function logout(Request $request): RedirectResponse
    {
        $request->session()->forget('guest_profile');

        return redirect()->route('guest.show')->with('guest_status', 'Guest session cleared.');
    }

    public function adAccess(Request $request): RedirectResponse
    {
        if (! session()->has('guest_profile')) {
            return redirect()->route('guest.show')->withErrors(['guest_login' => 'Sign in as a guest first.']);
        }

        $business = $this->business();
        $package = $business->packages()
            ->where('access_type', 'ad')
            ->where('is_active', true)
            ->firstOrFail();

        $data = session('guest_profile');

        $this->createSession($business, $package, $data, 'ad');

        return back()->with('guest_status', 'Access granted for 10 minutes after ad confirmation.');
    }

    public function subscriptionAccess(Request $request): RedirectResponse
    {
        if (! session()->has('guest_profile')) {
            return redirect()->route('guest.show')->withErrors(['guest_login' => 'Sign in as a guest first.']);
        }

        $business = $this->business();
        $data = $request->validate([
            'package_id' => ['required', 'exists:wifi_packages,id'],
            'payment_reference' => ['nullable', 'string', 'max:120'],
        ]) + session('guest_profile');

        $package = $business->packages()
            ->whereKey($data['package_id'])
            ->where('access_type', 'subscription')
            ->where('is_active', true)
            ->firstOrFail();

        $this->createSession($business, $package, $data, 'subscription');

        return back()->with('guest_status', 'Subscription access granted. Connect your device now.');
    }

    private function createSession(Business $business, WifiPackage $package, array $data, string $method): GuestSession
    {
        $startsAt = now();

        return GuestSession::create([
            'business_id' => $business->id,
            'wifi_package_id' => $package->id,
            'access_method' => $method,
            'guest_name' => $data['guest_name'] ?? null,
            'email' => $data['email'] ?? null,
            'phone' => $data['phone'] ?? null,
            'payment_reference' => $data['payment_reference'] ?? ($method === 'subscription' ? 'manual-'.Str::upper(Str::random(8)) : null),
            'amount_paid' => $method === 'subscription' ? $package->price : 0,
            'device_mac' => $data['device_mac'],
            'status' => 'active',
            'started_at' => $startsAt,
            'expires_at' => Carbon::instance($startsAt)->addMinutes($package->duration_minutes),
        ]);
    }

    private function business(): Business
    {
        return Business::query()
            ->where('company_name', 'Nextgen Technology')
            ->orWhere('site_name', 'Nextgen Public WiFi')
            ->firstOrFail();
    }
}
