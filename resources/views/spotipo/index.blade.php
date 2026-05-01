<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Spotipo Integration</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <main class="spotipo-page">
        <header class="spotipo-header">
            <div>
                <h1>Spotipo Integration</h1>
                <p>Admin-only test page for sites, voucher CRUD, and guest user CRUD.</p>
            </div>
            <nav>
                <a href="{{ route('portal.index') }}">Dashboard</a>
                <form method="post" action="{{ route('logout') }}">@csrf<button>Logout</button></form>
            </nav>
        </header>

        @if (session('status'))
            <div class="flash">{{ session('status') }}</div>
        @endif

        @if ($error)
            <div class="flash error">
                <strong>{{ $error['message'] }}</strong>
                @if ($error['status'])
                    <span>Status: {{ $error['status'] }}</span>
                @endif
                @if (! empty($error['context']))
                    <pre>{{ json_encode($error['context'], JSON_PRETTY_PRINT) }}</pre>
                @endif
            </div>
        @endif

        <section class="spotipo-grid">
            <article class="panel">
                <h2>Configuration</h2>
                <p class="panel-copy">Base URL: <code>{{ $baseUrl }}</code></p>
                <p class="panel-copy">Site ID: <code>{{ $siteId ?: 'not set' }}</code></p>
                <p class="panel-copy">Token: <code>{{ $configured ? 'configured' : 'missing' }}</code></p>
                <form method="post" action="{{ route('spotipo.test') }}">
                    @csrf
                    <button>Test Sites API</button>
                </form>
                @if ($sites)
                    <pre>{{ json_encode($sites, JSON_PRETTY_PRINT) }}</pre>
                @endif
            </article>

            <article class="panel">
                <h2>Create Voucher</h2>
                <form method="post" action="{{ route('spotipo.vouchers.store') }}" class="stack-form">
                    @csrf
                    <input name="num_to_create" type="number" min="1" value="1" placeholder="Number to create" required>
                    <input name="num_devices" type="number" min="1" value="1" placeholder="Devices" required>
                    <select name="duration_type"><option value="1">Minutes</option><option value="2">Hours</option><option value="3">Days</option></select>
                    <input name="duration_val" type="number" min="1" value="60" placeholder="Duration">
                    <input name="speed_dl" type="number" min="0" value="0" placeholder="Download speed">
                    <input name="speed_ul" type="number" min="0" value="0" placeholder="Upload speed">
                    <input name="bytes_t" type="number" min="0" value="0" placeholder="Data MB">
                    <input name="price" type="number" min="0" step="0.01" value="0" placeholder="Price">
                    <input name="notes" placeholder="Notes">
                    <label class="check-row"><input type="checkbox" name="unlimited_speed" value="1" checked> Unlimited speed</label>
                    <label class="check-row"><input type="checkbox" name="unlimited_data" value="1" checked> Unlimited data</label>
                    <button>Create Voucher</button>
                </form>
            </article>

            <article class="panel">
                <h2>Voucher Actions</h2>
                <form method="get" action="{{ route('spotipo.vouchers.index') }}" class="inline-form">
                    <input name="page" type="number" min="1" placeholder="Page">
                    <input name="per_page" type="number" min="1" max="100" placeholder="Per page">
                    <input name="search" placeholder="Search">
                    <button>List</button>
                </form>
                <form method="get" action="{{ url('/admin/spotipo/vouchers') }}" class="stack-form action-id-form">
                    <input name="voucher_id" placeholder="Voucher ID">
                    <button formaction="{{ url('/admin/spotipo/vouchers') }}" data-path="/admin/spotipo/vouchers/">Load Voucher</button>
                </form>
                @if ($vouchers)
                    <pre>{{ json_encode($vouchers, JSON_PRETTY_PRINT) }}</pre>
                @endif
            </article>

            <article class="panel">
                <h2>Create Guest User</h2>
                <form method="post" action="{{ route('spotipo.guest-users.store') }}" class="stack-form">
                    @csrf
                    <input name="username" placeholder="Username" required>
                    <input name="password" placeholder="Password">
                    <input name="num_devices" type="number" min="1" value="1" required>
                    <select name="duration_type"><option value="1">Minutes</option><option value="2">Hours</option><option value="3">Days</option></select>
                    <input name="duration_val" type="number" min="1" value="60" required>
                    <input name="speed_dl" type="number" min="0" value="0">
                    <input name="speed_ul" type="number" min="0" value="0">
                    <input name="bytes_t" type="number" min="0" value="0">
                    <select name="bytes_type"><option value="">Data period</option><option value="1">Total</option><option value="2">Daily</option><option value="3">Weekly</option><option value="4">Monthly</option></select>
                    <input name="notes" placeholder="Notes">
                    <label class="check-row"><input type="checkbox" name="unlimited_speed" value="1" checked> Unlimited speed</label>
                    <label class="check-row"><input type="checkbox" name="unlimited_data" value="1" checked> Unlimited data</label>
                    <button>Create Guest User</button>
                </form>
            </article>

            <article class="panel">
                <h2>Guest User Actions</h2>
                <form method="get" action="{{ route('spotipo.guest-users.index') }}" class="inline-form">
                    <input name="page" type="number" min="1" placeholder="Page">
                    <input name="per_page" type="number" min="1" max="100" placeholder="Per page">
                    <input name="search" placeholder="Search">
                    <button>List</button>
                </form>
                @if ($guestUsers)
                    <pre>{{ json_encode($guestUsers, JSON_PRETTY_PRINT) }}</pre>
                @endif
            </article>

            <article class="panel">
                <h2>Last Result</h2>
                @if ($result)
                    <pre>{{ json_encode($result, JSON_PRETTY_PRINT) }}</pre>
                @else
                    <p class="panel-copy">No Spotipo action has been run in this browser session.</p>
                @endif
            </article>
        </section>
    </main>
</body>
</html>
