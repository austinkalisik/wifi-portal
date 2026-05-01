# Nextgen Technology WiFi Access System

This Laravel application runs a WiFi access business portal for Nextgen Technology.

Project/runtime database name:

```text
nextgen-wifi-portal
```

It supports two guest access models:

- Watch Ads: guest watches an advert and receives 10 minutes of access.
- Subscription Plan: guest chooses a paid plan and receives access for the package duration.

## Main URLs

- Admin portal: `/`
- Admin login: `/login`
- Guest captive portal page: `/guest`
- Health check: `/up`
- API base: `/api/portal`

## Default Admin Login

Seeded local administrator:

```text
Email: admin@nextgentechnology.local
Password: Nextgen@12345
```

Change this password before production.

## Current Stack

- Laravel 13 backend
- Blade admin and guest pages
- Vite/Tailwind CSS assets
- MySQL database: `nextgen-wifi-portal`
- Sanctum installed for future user auth/API token hardening

The local app is configured in `.env` to use MySQL:

```text
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextgen-wifi-portal
DB_USERNAME=root
DB_PASSWORD=
```

## Core Data Model

- `businesses`: company profile, site profile, generated API key
- `branding_profiles`: splash/guest page colors and text
- `wifi_packages`: ad access and subscription plans
- `routers`: router registration, vendor, shared secret, status
- `customer_devices`: device whitelist/blocklist
- `guest_sessions`: granted ad/subscription access sessions

## Access Logic

Guests must first log in at `/guest` with:

- Name
- Optional phone
- Optional email
- Device MAC address

After guest login, the page shows two choices:

- Watch Ads
- Subscription Plan

### Watch Ads

Default package:

- Name: `Ad Sponsored Access`
- Type: `ad`
- Required ad watch: 30 seconds
- Access duration: 10 minutes
- Price: 0

Guest page form:

```text
POST /guest/login
guest_name=Guest User
device_mac=AA:BB:CC:DD:EE:01

POST /guest/ad-access
```

API form:

```http
POST /api/portal/ad-access
X-Portal-Key: YOUR_API_KEY
Content-Type: application/json

{
  "device_mac": "AA:BB:CC:DD:EE:01",
  "ad_reference": "AD-NETWORK-IMPRESSION-ID"
}
```

### Subscription

Default packages:

- `Hourly Plan`: 60 minutes, PGK 3
- `Day Plan`: 1440 minutes, PGK 10

Guest page form:

```text
POST /guest/login
guest_name=Guest User
device_mac=AA:BB:CC:DD:EE:02

POST /guest/subscription-access
package_id=1
payment_reference=PAYMENT-ID
```

API form:

```http
POST /api/portal/subscription-access
X-Portal-Key: YOUR_API_KEY
Content-Type: application/json

{
  "package_id": 2,
  "device_mac": "AA:BB:CC:DD:EE:02",
  "phone": "+67500000000",
  "payment_reference": "PAYMENT-ID"
}
```

## API Endpoints

All API endpoints require:

```text
X-Portal-Key: the business API key shown in Admin > API Connect
```

Routes:

```text
GET  /api/portal/config
GET  /api/portal/routers
POST /api/portal/router-heartbeat
POST /api/portal/sessions
POST /api/portal/ad-access
POST /api/portal/subscription-access
```

Router heartbeat example:

```http
POST /api/portal/router-heartbeat
X-Portal-Key: YOUR_API_KEY
Content-Type: application/json

{
  "shared_secret": "ROUTER_SECRET_FROM_ADMIN",
  "ip_address": "192.168.88.1",
  "status": "online"
}
```

## Local Development

Install dependencies:

```bash
composer install
npm install
```

Prepare database:

```bash
copy .env.example .env
php artisan key:generate
php artisan config:clear
php artisan migrate --seed
```

If the database already exists in phpMyAdmin but has no tables, run:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Run app:

```bash
php artisan serve --host=192.168.31.34 --port=8002
npm run dev -- --host 192.168.31.34 --port 5174
```

Build assets:

```bash
npm run build
```

Run tests:

```bash
php artisan test
```

## Manual Browser Test

1. Open `/login`.
2. Sign in with `admin@nextgentechnology.local` and `Nextgen@12345`.
3. Confirm dashboard loads.
4. Open `/guest`.
5. Enter a guest name and device MAC address.
6. Confirm the two options appear: `Watch ads` and `Subscription plan`.
7. Click `Watch Ad and Connect`.
8. Return to admin dashboard and confirm a recent `ad` guest session exists.

## Manual API Test

Copy the API key from Admin > API Connect, then call:

```bash
curl -H "X-Portal-Key: YOUR_API_KEY" http://your-domain/api/portal/config
```

Current local MySQL seeded API key:

```text
wp_ucvPN0MFpNwHSAPns0m7HWtHeEC5UoZ4A78onFzF
```

Create ad access:

```bash
curl -X POST http://your-domain/api/portal/ad-access \
  -H "X-Portal-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d "{\"device_mac\":\"AA:BB:CC:DD:EE:01\",\"ad_reference\":\"AD-TEST-001\"}"
```

Create subscription access:

```bash
curl -X POST http://your-domain/api/portal/subscription-access \
  -H "X-Portal-Key: YOUR_API_KEY" \
  -H "Content-Type: application/json" \
  -d "{\"package_id\":2,\"device_mac\":\"AA:BB:CC:DD:EE:02\",\"payment_reference\":\"PAY-TEST-001\"}"
```

## Production Deployment Checklist

1. Set `APP_ENV=production`.
2. Set `APP_DEBUG=false`.
3. Set `APP_URL=https://your-domain`.
4. Use MySQL or PostgreSQL, not SQLite.
5. Run `php artisan migrate --force`.
6. Run `npm run build`.
7. Run:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

8. Put the web server document root at `public/`.
9. Use HTTPS.
10. Store API keys and payment secrets only in `.env`.
11. Add a real payment gateway webhook before accepting real automated payments.

## What To Build Next

This system is ready as a local working MVP. For commercial production, the next engineering steps are:

- Admin login and roles with Laravel Sanctum or Breeze/Fortify
- Real payment gateway integration and signed webhooks
- Real ad network callback verification before granting ad access
- Router-specific enforcement scripts for MikroTik/Ubiquiti/Cisco
- Audit logs for admin and API activity
- Multi-tenant accounts if Nextgen manages many customer locations
