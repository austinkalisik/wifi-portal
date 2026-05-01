# Proposal: Nextgen WiFi Portal + Spotipo Integration

## Goal

Upgrade the existing Laravel 13 WiFi portal to integrate with Spotipo while preserving the current Nextgen Technology admin dashboard and guest portal.

## Recommended Architecture

Keep Laravel as the authority for:

- Admin authentication
- Business configuration
- Guest portal presentation
- Local reporting
- API security
- Provider selection

Use Spotipo as an external WiFi access backend for:

- Site discovery
- Voucher management
- Guest user account management

## Why This Design

The integration is isolated behind a provider contract:

```text
WifiAccessProvider
```

This prevents the app from becoming tightly coupled to Spotipo. If Nextgen later adds MikroTik, UniFi, RADIUS, or another captive portal provider, a new provider can be added without rewriting the admin or guest system.

## Implementation Summary

- Added `.env` and `config/services.php` Spotipo settings.
- Added `SpotipoClient` with `Authentication-Token` header.
- Added `SpotipoProvider` for Spotipo CRUD.
- Added `LocalWifiAccessProvider` to preserve current behavior.
- Added admin-only Spotipo test page.
- Added voucher CRUD routes.
- Added guest user CRUD routes.
- Added graceful API error handling.
- Added mocked HTTP feature tests.
- Added documentation.

## Security Notes

- No Spotipo token is hardcoded.
- No token is rendered in admin pages.
- Admin Spotipo routes are behind `auth` middleware.
- All admin writes use Laravel validation.
- Tests use mocked HTTP and do not require real credentials.

## Production Rollout

1. Create a Spotipo API key in Spotipo.
2. Add it to production `.env` as `SPOTIPO_AUTH_TOKEN`.
3. Set `SPOTIPO_SITE_ID`.
4. Run `php artisan optimize:clear`.
5. Login to `/admin/spotipo`.
6. Test sites endpoint.
7. Test voucher creation/deletion.
8. Test guest user creation/deletion.
9. Switch `WIFI_ACCESS_PROVIDER=spotipo` only after validation.

## Future Work

- Map guest portal subscription purchases directly to Spotipo guest users.
- Map watch-ad access directly to short-lived Spotipo vouchers or guest users.
- Add audit log records for every Spotipo operation.
- Add per-site configuration for multi-location deployments.
- Add role permissions for support staff versus owners.
