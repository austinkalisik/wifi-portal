<?php

namespace Tests\Feature;

use App\Models\Business;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_application_returns_a_successful_response(): void
    {
        $this->actingAs($this->adminUser());

        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('API Connect');
        $response->assertSee('Ad and Subscription Plans');
    }

    public function test_admin_login_protects_dashboard(): void
    {
        $this->get('/')->assertRedirect('/login');

        $this->adminUser();
        $response = $this->post('/login', [
            'email' => 'admin@nextgentechnology.local',
            'password' => 'Nextgen@12345',
        ]);

        $response->assertRedirect('/');
        $this->assertAuthenticated();
    }

    public function test_portal_api_returns_config_with_valid_key(): void
    {
        $this->actingAs($this->adminUser());
        $this->get('/');
        $business = Business::firstOrFail();

        $response = $this->withHeader('X-Portal-Key', $business->api_key)
            ->getJson('/api/portal/config');

        $response->assertOk()
            ->assertJsonPath('business.site_name', 'Nextgen Public WiFi')
            ->assertJsonCount(3, 'packages');
    }

    public function test_portal_api_rejects_invalid_key(): void
    {
        $response = $this->withHeader('X-Portal-Key', 'bad-key')
            ->getJson('/api/portal/config');

        $response->assertUnauthorized();
    }

    public function test_guest_can_create_ad_access_session(): void
    {
        $this->actingAs($this->adminUser());
        $this->get('/');

        $this->post('/guest/login', [
            'guest_name' => 'Guest User',
            'device_mac' => 'AA:BB:CC:DD:EE:01',
        ])->assertRedirect('/guest');

        $response = $this->post('/guest/ad-access', [
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('guest_sessions', [
            'device_mac' => 'AA:BB:CC:DD:EE:01',
            'access_method' => 'ad',
            'amount_paid' => 0,
        ]);
    }

    public function test_api_can_create_subscription_session(): void
    {
        $this->actingAs($this->adminUser());
        $this->get('/');
        $business = Business::where('site_name', 'Nextgen Public WiFi')->firstOrFail();
        $package = $business->packages()->where('access_type', 'subscription')->firstOrFail();

        $response = $this->withHeader('X-Portal-Key', $business->api_key)
            ->postJson('/api/portal/subscription-access', [
                'package_id' => $package->id,
                'device_mac' => 'AA:BB:CC:DD:EE:02',
            'payment_reference' => 'TEST-PAY-001',
        ]);

        $response->assertCreated()
            ->assertJsonPath('access_method', 'subscription');
        $this->assertDatabaseHas('guest_sessions', [
            'device_mac' => 'AA:BB:CC:DD:EE:02',
            'payment_reference' => 'TEST-PAY-001',
        ]);
    }

    private function adminUser(): User
    {
        return User::query()->updateOrCreate(
            ['email' => 'admin@nextgentechnology.local'],
            [
                'name' => 'Nextgen Administrator',
                'password' => Hash::make('Nextgen@12345'),
            ]
        );
    }
}
