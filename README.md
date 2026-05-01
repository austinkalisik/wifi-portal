# Nextgen WiFi Portal

Laravel 13 WiFi access portal for **Nextgen Technology**.

The system provides an admin dashboard, a guest captive portal flow, local access-plan management, API endpoints for routers/external systems, and an optional Spotipo integration for sites, vouchers, and guest users.

## Features

- Admin login and protected dashboard
- Guest login page
- Guest access options:
  - Watch ads for short access
  - Select a subscription plan for paid access
- Business profile and splash page branding
- Ad/subscription plan management
- Router registration and heartbeat API
- Device whitelist/blocklist
- Guest session tracking
- MySQL database support
- Spotipo API integration:
  - Sites test
  - Voucher CRUD
  - Guest user CRUD
  - `Authentication-Token` header
  - Mocked feature tests

## Tech Stack

- Laravel 13
- PHP 8.3+
- MySQL
- Vite
- Tailwind CSS
- Laravel HTTP client
- PHPUnit feature tests

## Requirements

- PHP 8.3 or newer
- Composer
- Node.js and npm
- MySQL or MariaDB
- A database named:

```text
nextgen-wifi-portal
```

## Installation

Clone the repository:

```bash
git clone https://github.com/austinkalisik/wifi-portal.git
cd wifi-portal
```

Install dependencies:

```bash
composer install
npm install
```

Create environment file:

```bash
copy .env.example .env
php artisan key:generate
```

Configure database in `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nextgen-wifi-portal
DB_USERNAME=root
DB_PASSWORD=
```

Run migrations and seed default data:

```bash
php artisan migrate --force
php artisan db:seed --force
```

Build frontend assets:

```bash
npm run build
```

Run the local server:

```bash
php artisan serve --host=127.0.0.1 --port=8002
```

For LAN testing, use your machine IP:

```bash
php artisan serve --host=192.168.31.34 --port=8002
```

## Default Login

Admin login URL:

```text
/login
```

Seeded local admin:

```text
Email: admin@nextgentechnology.local
Password: Nextgen@12345
```

Change this password before production.

## Main URLs

```text
/                  Admin dashboard, requires login
/login             Admin login
/guest             Guest captive portal
/admin/spotipo     Admin-only Spotipo integration page
/api/portal/*      Local portal API endpoints
```

## Guest Flow

1. Guest opens `/guest`.
2. Guest enters name and device MAC address.
3. System shows two access choices:
   - `Watch Ad and Connect`
   - `Subscribe and Connect`
4. The selected action creates a `guest_sessions` record.

## Local API

All portal API requests require:

```text
X-Portal-Key: business-api-key
```

Endpoints:

```text
GET  /api/portal/config
GET  /api/portal/routers
POST /api/portal/router-heartbeat
POST /api/portal/sessions
POST /api/portal/ad-access
POST /api/portal/subscription-access
```

## Spotipo Configuration

Add your Spotipo settings to `.env`:

```env
WIFI_ACCESS_PROVIDER=local
SPOTIPO_BASE_URL=https://api.spotipo.com
SPOTIPO_AUTH_TOKEN=
SPOTIPO_SITE_ID=
SPOTIPO_TIMEOUT=15
```

Do not commit real Spotipo tokens.

After changing `.env`, run:

```bash
php artisan optimize:clear
```

Spotipo admin page:

```text
/admin/spotipo
```

Spotipo integration documentation:

- `README_SPOTIPO_UPGRADE.md`
- `PROPOSAL.md`

## Tests

Run all tests:

```bash
php artisan test
```

Run only Spotipo tests:

```bash
php artisan test --filter=SpotipoIntegrationTest
```

The Spotipo tests use Laravel `Http::fake()` and do not call the real Spotipo API.

## Documentation

Additional project docs:

- `NEXTGEN_SYSTEM_GUIDE.md`
- `README_SPOTIPO_UPGRADE.md`
- `PROPOSAL.md`

## Production Checklist

- Set `APP_ENV=production`
- Set `APP_DEBUG=false`
- Set a real `APP_URL`
- Use HTTPS
- Change the default admin password
- Use a secure MySQL user, not root
- Configure real payment gateway webhook verification
- Configure real ad-network callback verification
- Configure router enforcement scripts for the target router hardware
- Store all secrets in `.env`
- Run:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build
```
