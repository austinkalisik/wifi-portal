<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SpotipoIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.spotipo.base_url' => 'https://api.spotipo.com',
            'services.spotipo.auth_token' => 'test-token',
            'services.spotipo.site_id' => '123',
        ]);
    }

    public function test_spotipo_admin_page_is_admin_only(): void
    {
        $this->get('/admin/spotipo')->assertRedirect('/login');

        $this->actingAs($this->adminUser())
            ->get('/admin/spotipo')
            ->assertOk()
            ->assertSee('Spotipo Integration')
            ->assertSee('Token:')
            ->assertDontSee('test-token');
    }

    public function test_sites_request_uses_authentication_token_header(): void
    {
        Http::fake([
            'https://api.spotipo.com/ext/api/v1/sites/' => Http::response([
                ['id' => 123, 'name' => 'Nextgen Site'],
            ]),
        ]);

        $this->actingAs($this->adminUser())
            ->post('/admin/spotipo/test')
            ->assertRedirect();

        Http::assertSent(fn ($request): bool => $request->url() === 'https://api.spotipo.com/ext/api/v1/sites/'
            && $request->method() === 'GET'
            && $request->header('Authentication-Token')[0] === 'test-token');
    }

    public function test_voucher_crud_uses_spotipo_endpoints(): void
    {
        Http::fake([
            'https://api.spotipo.com/ext/123/api/v1/voucher/' => Http::response(['id' => 10, 'code' => 'ABC']),
            'https://api.spotipo.com/ext/123/api/v1/voucher/10' => Http::sequence()
                ->push(['id' => 10, 'code' => 'ABC'])
                ->push(['id' => 10, 'notes' => 'updated'])
                ->push(['ok' => true]),
        ]);

        $user = $this->adminUser();
        $this->actingAs($user)->post('/admin/spotipo/vouchers', [
            'num_to_create' => 1,
            'num_devices' => 1,
            'duration_type' => 1,
            'duration_val' => 60,
            'unlimited_speed' => 1,
            'unlimited_data' => 1,
        ])->assertRedirect();

        $this->actingAs($user)->get('/admin/spotipo/vouchers/10')->assertRedirect();

        $this->actingAs($user)->put('/admin/spotipo/vouchers/10', [
            'num_devices' => 1,
            'duration_type' => 1,
            'duration_val' => 30,
            'notes' => 'updated',
        ])->assertRedirect();

        $this->actingAs($user)->delete('/admin/spotipo/vouchers/10')->assertRedirect();

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && $request->url() === 'https://api.spotipo.com/ext/123/api/v1/voucher/');
        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && $request->url() === 'https://api.spotipo.com/ext/123/api/v1/voucher/10');
        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && $request->url() === 'https://api.spotipo.com/ext/123/api/v1/voucher/10');
        Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
            && $request->url() === 'https://api.spotipo.com/ext/123/api/v1/voucher/10');
    }

    public function test_guest_user_crud_uses_spotipo_endpoints(): void
    {
        Http::fake([
            'https://api.spotipo.com/ext/123/api/v1/guestuser/' => Http::response(['username' => 'guest-1']),
            'https://api.spotipo.com/ext/123/api/v1/guestuser/u/guest-1' => Http::sequence()
                ->push(['username' => 'guest-1'])
                ->push(['username' => 'guest-1', 'notes' => 'updated'])
                ->push(['ok' => true]),
        ]);

        $user = $this->adminUser();
        $payload = [
            'username' => 'guest-1',
            'num_devices' => 1,
            'duration_type' => 1,
            'duration_val' => 60,
            'unlimited_speed' => 1,
            'unlimited_data' => 1,
        ];

        $this->actingAs($user)->post('/admin/spotipo/guest-users', $payload)->assertRedirect();
        $this->actingAs($user)->get('/admin/spotipo/guest-users/guest-1')->assertRedirect();
        $this->actingAs($user)->put('/admin/spotipo/guest-users/guest-1', $payload + ['notes' => 'updated'])->assertRedirect();
        $this->actingAs($user)->delete('/admin/spotipo/guest-users/guest-1')->assertRedirect();

        Http::assertSent(fn ($request): bool => $request->method() === 'POST'
            && $request->url() === 'https://api.spotipo.com/ext/123/api/v1/guestuser/');
        Http::assertSent(fn ($request): bool => $request->method() === 'GET'
            && $request->url() === 'https://api.spotipo.com/ext/123/api/v1/guestuser/u/guest-1');
        Http::assertSent(fn ($request): bool => $request->method() === 'PUT'
            && $request->url() === 'https://api.spotipo.com/ext/123/api/v1/guestuser/u/guest-1');
        Http::assertSent(fn ($request): bool => $request->method() === 'DELETE'
            && $request->url() === 'https://api.spotipo.com/ext/123/api/v1/guestuser/u/guest-1');
    }

    public function test_spotipo_errors_are_returned_gracefully(): void
    {
        Http::fake([
            'https://api.spotipo.com/ext/api/v1/sites/' => Http::response(['error' => 'bad token'], 401),
        ]);

        $this->actingAs($this->adminUser())
            ->from('/admin/spotipo')
            ->post('/admin/spotipo/test')
            ->assertRedirect('/admin/spotipo')
            ->assertSessionHas('spotipo_error');
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
