<?php

namespace App\Http\Controllers;

use App\Models\Business;
use App\Models\CustomerDevice;
use App\Models\Router;
use App\Models\WifiPackage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PortalController extends Controller
{
    public function index(): View
    {
        $business = $this->business();

        $business->load([
            'brandingProfile',
            'packages' => fn ($query) => $query->latest(),
            'routers' => fn ($query) => $query->latest(),
            'devices' => fn ($query) => $query->latest(),
            'sessions' => fn ($query) => $query->latest()->limit(8),
        ]);

        $completed = collect([
            filled($business->brandingProfile?->welcome_headline),
            $business->packages->where('is_active', true)->isNotEmpty(),
            $business->routers->whereIn('status', ['pending', 'online'])->isNotEmpty(),
            $business->devices->isNotEmpty(),
        ])->filter()->count();

        return view('welcome', [
            'business' => $business,
            'branding' => $business->brandingProfile,
            'packages' => $business->packages,
            'routers' => $business->routers,
            'devices' => $business->devices,
            'sessions' => $business->sessions,
            'completed' => $completed,
        ]);
    }

    public function updateBusiness(Request $request): RedirectResponse
    {
        $business = $this->business();
        $data = $request->validate([
            'company_name' => ['required', 'string', 'max:120'],
            'site_name' => ['required', 'string', 'max:120'],
            'contact_email' => ['nullable', 'email', 'max:160'],
            'phone' => ['nullable', 'string', 'max:40'],
            'currency' => ['required', 'string', 'size:3'],
            'timezone' => ['required', 'string', 'max:80'],
        ]);

        $business->update($data);

        return back()->with('status', 'Business profile saved.');
    }

    public function updateBranding(Request $request): RedirectResponse
    {
        $business = $this->business();
        $data = $request->validate([
            'logo_url' => ['nullable', 'url', 'max:255'],
            'primary_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'accent_color' => ['required', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'welcome_headline' => ['required', 'string', 'max:120'],
            'welcome_message' => ['nullable', 'string', 'max:500'],
            'terms_url' => ['nullable', 'url', 'max:255'],
        ]);

        $business->brandingProfile()->updateOrCreate(
            ['business_id' => $business->id],
            $data
        );

        return back()->with('status', 'Splash branding saved.');
    }

    public function storePackage(Request $request): RedirectResponse
    {
        $business = $this->business();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'access_type' => ['required', 'in:ad,subscription'],
            'description' => ['nullable', 'string', 'max:300'],
            'duration_minutes' => ['required', 'integer', 'min:5', 'max:43200'],
            'ad_watch_seconds' => ['nullable', 'integer', 'min:5', 'max:600'],
            'price' => ['required', 'numeric', 'min:0', 'max:999999'],
            'download_mbps' => ['required', 'integer', 'min:1', 'max:10000'],
            'upload_mbps' => ['required', 'integer', 'min:1', 'max:10000'],
        ]);

        $business->packages()->create($data + ['is_active' => true]);

        return back()->with('status', 'WiFi package added.');
    }

    public function togglePackage(WifiPackage $package): RedirectResponse
    {
        $package->update(['is_active' => ! $package->is_active]);

        return back()->with('status', 'Package status updated.');
    }

    public function storeRouter(Request $request): RedirectResponse
    {
        $business = $this->business();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'vendor' => ['required', 'string', 'max:80'],
            'mac_address' => ['nullable', 'string', 'max:32'],
            'ip_address' => ['nullable', 'ip'],
        ]);

        $business->routers()->create($data + [
            'shared_secret' => 'rt_'.Str::random(32),
            'status' => 'pending',
        ]);

        return back()->with('status', 'Router registered.');
    }

    public function storeDevice(Request $request): RedirectResponse
    {
        $business = $this->business();
        $data = $request->validate([
            'router_id' => ['nullable', 'exists:routers,id'],
            'mac_address' => ['required', 'string', 'max:32'],
            'label' => ['nullable', 'string', 'max:100'],
            'status' => ['required', 'in:allowed,blocked'],
        ]);

        $business->devices()->create($data);

        return back()->with('status', 'Device saved.');
    }

    private function business(): Business
    {
        $business = Business::firstOrCreate(
            ['site_name' => 'Nextgen Public WiFi'],
            [
                'company_name' => 'Nextgen Technology',
                'contact_email' => 'support@nextgentechnology.local',
                'phone' => '+675 0000 0000',
                'currency' => 'PGK',
                'timezone' => 'Pacific/Port_Moresby',
            ]
        );

        $business->update([
            'company_name' => 'Nextgen Technology',
            'site_name' => 'Nextgen Public WiFi',
        ]);

        $business->brandingProfile()->firstOrCreate(
            ['business_id' => $business->id],
            [
                'primary_color' => '#2563eb',
                'accent_color' => '#0f172a',
                'welcome_headline' => 'Connect with Nextgen Technology WiFi',
                'welcome_message' => 'Watch ads for 10 minutes of access or choose a subscription plan for longer premium internet.',
            ]
        );

        if ($business->packages()->doesntExist()) {
            $business->packages()->createMany([
                [
                    'name' => 'Ad Sponsored Access',
                    'access_type' => 'ad',
                    'description' => 'Guest watches an advert and receives 10 minutes of access.',
                    'duration_minutes' => 10,
                    'ad_watch_seconds' => 30,
                    'price' => 0,
                    'download_mbps' => 5,
                    'upload_mbps' => 2,
                    'is_active' => true,
                ],
                [
                    'name' => 'Hourly Plan',
                    'access_type' => 'subscription',
                    'description' => 'Paid hourly internet access.',
                    'duration_minutes' => 60,
                    'ad_watch_seconds' => null,
                    'price' => 3,
                    'download_mbps' => 15,
                    'upload_mbps' => 5,
                    'is_active' => true,
                ],
                [
                    'name' => 'Day Plan',
                    'access_type' => 'subscription',
                    'description' => 'Paid all-day internet access.',
                    'duration_minutes' => 1440,
                    'ad_watch_seconds' => null,
                    'price' => 10,
                    'download_mbps' => 20,
                    'upload_mbps' => 10,
                    'is_active' => true,
                ],
            ]);
        }

        return $business;
    }
}
