<?php

namespace Database\Seeders;

use App\Models\Business;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'admin@nextgentechnology.local'],
            [
                'name' => 'Nextgen Administrator',
                'password' => Hash::make('Nextgen@12345'),
            ]
        );

        $business = Business::firstOrCreate(
            ['site_name' => 'Nextgen Public WiFi'],
            [
                'company_name' => 'Nextgen Technology',
                'contact_email' => 'support@nextgentechnology.local',
                'phone' => '+675 0000 0000',
                'currency' => 'PGK',
                'timezone' => 'Pacific/Port_Moresby',
                'api_key' => 'wp_'.Str::random(40),
            ]
        );

        $business->brandingProfile()->firstOrCreate(
            ['business_id' => $business->id],
            [
                'primary_color' => '#2563eb',
                'accent_color' => '#0f172a',
                'welcome_headline' => 'Connect with Nextgen Technology WiFi',
                'welcome_message' => 'Watch ads for 10 minutes of access or choose a subscription plan for longer premium internet.',
            ]
        );

        $business->packages()->firstOrCreate(
            ['name' => 'Ad Sponsored Access'],
            [
                'access_type' => 'ad',
                'description' => 'Guest watches an advert and receives 10 minutes of access.',
                'duration_minutes' => 10,
                'ad_watch_seconds' => 30,
                'price' => 0,
                'download_mbps' => 5,
                'upload_mbps' => 2,
                'is_active' => true,
            ]
        );

        $business->packages()->firstOrCreate(
            ['name' => 'Hourly Plan'],
            [
                'access_type' => 'subscription',
                'description' => 'Paid hourly internet access.',
                'duration_minutes' => 60,
                'ad_watch_seconds' => null,
                'price' => 3,
                'download_mbps' => 15,
                'upload_mbps' => 5,
                'is_active' => true,
            ]
        );

        $business->packages()->firstOrCreate(
            ['name' => 'Day Plan'],
            [
                'access_type' => 'subscription',
                'description' => 'Paid all-day internet access.',
                'duration_minutes' => 1440,
                'ad_watch_seconds' => null,
                'price' => 10,
                'download_mbps' => 20,
                'upload_mbps' => 10,
                'is_active' => true,
            ]
        );
    }
}
