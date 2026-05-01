# Spotipo Upgrade Guide

This upgrade keeps the existing Laravel 13 WiFi portal, admin dashboard, guest portal, MySQL database, and local access behavior. It adds a Spotipo integration layer that can be enabled without rebuilding the app.

## Configuration

Add these values to `.env`:

```env
WIFI_ACCESS_PROVIDER=local
SPOTIPO_BASE_URL=https://api.spotipo.com
SPOTIPO_AUTH_TOKEN=
SPOTIPO_SITE_ID=
SPOTIPO_TIMEOUT=15
```

Use `WIFI_ACCESS_PROVIDER=spotipo` only when you want app services to resolve the Spotipo provider by default. The admin Spotipo test page always uses the Spotipo provider directly.

Never commit a real `SPOTIPO_AUTH_TOKEN`.

## Spotipo API Coverage

Configured endpoints:

```text
GET    /ext/api/v1/sites/
POST   /ext/{siteid}/api/v1/voucher/
GET    /ext/{siteid}/api/v1/voucher/{voucherid}
PUT    /ext/{siteid}/api/v1/voucher/{voucherid}
DELETE /ext/{siteid}/api/v1/voucher/{voucherid}
GET    /ext/{siteid}/api/v1/guestuser/
POST   /ext/{siteid}/api/v1/guestuser/
GET    /ext/{siteid}/api/v1/guestuser/u/{username}
PUT    /ext/{siteid}/api/v1/guestuser/u/{username}
DELETE /ext/{siteid}/api/v1/guestuser/u/{username}
```

Every request uses:

```text
Authentication-Token: your-spotipo-token
```

## Admin Test Page

Login as an admin, then open:

```text
/admin/spotipo
```

The page is protected by Laravel `auth` middleware. It can:

- Test the Spotipo sites endpoint
- Create vouchers
- Read/update/delete vouchers by ID through routes
- List guest users
- Create guest users
- Read/update/delete guest users by username through routes
- Show safe error messages without leaking the API token

## Laravel Classes Added

```text
app/Services/Spotipo/SpotipoClient.php
app/Services/Spotipo/SpotipoProvider.php
app/Services/Spotipo/SpotipoApiException.php
app/Services/WifiAccess/WifiAccessProvider.php
app/Services/WifiAccess/LocalWifiAccessProvider.php
app/Http/Controllers/SpotipoAdminController.php
resources/views/spotipo/index.blade.php
tests/Feature/SpotipoIntegrationTest.php
```

## Provider Abstraction

`WifiAccessProvider` defines a small interface for sites, vouchers, and guest users.

Current implementations:

- `LocalWifiAccessProvider`: keeps existing portal behavior available.
- `SpotipoProvider`: calls Spotipo using `SpotipoClient`.

`AppServiceProvider` binds the provider based on:

```env
WIFI_ACCESS_PROVIDER=local
```

or:

```env
WIFI_ACCESS_PROVIDER=spotipo
```

## Feature Tests

The Spotipo tests use Laravel `Http::fake()` and do not call the real Spotipo API.

Run:

```bash
php artisan test --filter=SpotipoIntegrationTest
```

Full suite:

```bash
php artisan test
```

## Graceful Error Handling

`SpotipoClient` throws `SpotipoApiException` for:

- Missing config
- Connection failures
- Non-2xx API responses

The admin controller catches the exception, stores a safe error in the session, and redirects back to the Spotipo admin page. The token is never displayed.

## Manual Test Checklist

1. Add `SPOTIPO_AUTH_TOKEN` and `SPOTIPO_SITE_ID` to `.env`.
2. Run `php artisan optimize:clear`.
3. Login at `/login`.
4. Open `/admin/spotipo`.
5. Click `Test Sites API`.
6. Create a small test voucher.
7. Create a test guest user.
8. Confirm Spotipo shows the created resources.
9. Delete test resources.

## Existing Behavior Preserved

The existing admin dashboard, guest login, watch-ad access, subscription access, MySQL data model, and local API endpoints are unchanged. Spotipo is additive.
