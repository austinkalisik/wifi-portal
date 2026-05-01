<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $business->company_name }} Admin Portal</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body>
    <div class="portal-shell">
        <aside class="sidebar">
            <div class="brand-mark" aria-label="Nextgen Technology"><span></span></div>
            <nav class="main-nav" aria-label="Primary">
                <a class="nav-item active" href="#setup"><svg viewBox="0 0 24 24"><path d="M5 12h14M12 5v14"/></svg><span>Start Here</span><i></i></a>
                <a class="nav-item" href="#dashboard"><svg viewBox="0 0 24 24"><path d="M4 11 12 4l8 7v8a1 1 0 0 1-1 1h-5v-6h-4v6H5a1 1 0 0 1-1-1z"/></svg><span>Dashboard</span></a>
                <a class="nav-item" href="#branding"><svg viewBox="0 0 24 24"><path d="M4 5h16v10H4zM8 19h8M12 15v4"/></svg><span>Splash Page</span></a>
                <a class="nav-item" href="#packages"><svg viewBox="0 0 24 24"><path d="M12 3a9 9 0 1 0 0 18 9 9 0 0 0 0-18zM8 12h8M12 8v8"/></svg><span>Plans</span></a>
                <a class="nav-item" href="#api"><svg viewBox="0 0 24 24"><path d="m4 14 14-6v8L4 14zm0 0v5"/></svg><span>API Connect</span></a>
                <a class="nav-item" href="#routers"><svg viewBox="0 0 24 24"><path d="M12 8a4 4 0 1 0 0 8 4 4 0 0 0 0-8zM12 2v3M12 19v3M4.9 4.9 7 7M17 17l2.1 2.1M2 12h3M19 12h3M4.9 19.1 7 17M17 7l2.1-2.1"/></svg><span>Routers</span></a>
            </nav>
            <div class="sidebar-footer">
                <form method="post" action="{{ route('logout') }}" class="logout-form">
                    @csrf
                    <button>Logout</button>
                </form>
                <button type="button" class="property-switch">
                    <span>{{ $business->site_name }}</span>
                    <svg viewBox="0 0 24 24"><path d="m8 10 4-4 4 4M16 14l-4 4-4-4"/></svg>
                </button>
            </div>
        </aside>

        <main class="content">
            <div class="trial-banner">
                <span>i</span>
                {{ $business->company_name }} access system: ads every 10 minutes, subscription plans, router API, and guest sessions.
            </div>

            <section class="setup" id="setup">
                @if (session('status'))
                    <div class="flash">{{ session('status') }}</div>
                @endif
                @if ($errors->any())
                    <div class="flash error">
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                @endif

                <h1>Nextgen Technology WiFi Access Platform</h1>
                <p>Manage the business profile, guest splash page, ad-sponsored access, paid subscriptions, routers, devices, sessions, and API integrations.</p>

                <div class="progress-copy">{{ $completed }} / 4 completed</div>
                <div class="progress-track"><span style="width: {{ $completed * 25 }}%"></span></div>

                <div class="steps">
                    <article class="step {{ $completed >= 1 ? '' : 'current' }}">
                        <div class="step-number">1</div>
                        <div><h2>Brand the splash page</h2><p>{{ $branding->welcome_headline }}</p><a class="button-link" href="#branding">Customize Splash Page</a></div>
                    </article>
                    <article class="step {{ $completed >= 2 ? '' : 'muted' }}">
                        <div class="step-number">2</div>
                        <div><h2>Create access plans</h2><p>{{ $packages->where('is_active', true)->count() }} active option(s): ads and subscriptions.</p><a class="button-link" href="#packages">Manage Plans</a></div>
                    </article>
                    <article class="step {{ $completed >= 3 ? '' : 'muted' }}">
                        <div class="step-number">3</div>
                        <div><h2>Connect routers</h2><p>{{ $routers->count() }} router(s) registered. Routers use API key plus router secret.</p><a class="button-link" href="#routers">Router Setup</a></div>
                    </article>
                    <article class="step {{ $completed >= 4 ? '' : 'muted' }}">
                        <div class="step-number">4</div>
                        <div><h2>Open guest access</h2><p>Guests use <a href="{{ route('guest.show') }}">/guest</a> to watch ads or subscribe.</p><a class="button-link" href="{{ route('guest.show') }}">View Guest Page</a></div>
                    </article>
                </div>

                <section class="metrics" id="dashboard">
                    <div><strong>{{ $packages->where('access_type', 'ad')->where('is_active', true)->count() }}</strong><span>Ad access plans</span></div>
                    <div><strong>{{ $packages->where('access_type', 'subscription')->where('is_active', true)->count() }}</strong><span>Subscription plans</span></div>
                    <div><strong>{{ $routers->where('status', 'online')->count() }}</strong><span>Online routers</span></div>
                    <div><strong>{{ $sessions->count() }}</strong><span>Recent sessions</span></div>
                </section>

                <section class="work-grid">
                    <form class="panel" method="post" action="{{ route('portal.business.update') }}">
                        @csrf
                        <h2>Business Profile</h2>
                        <label>Company name<input name="company_name" value="{{ old('company_name', $business->company_name) }}" required></label>
                        <label>Site name<input name="site_name" value="{{ old('site_name', $business->site_name) }}" required></label>
                        <label>Email<input name="contact_email" type="email" value="{{ old('contact_email', $business->contact_email) }}"></label>
                        <label>Phone<input name="phone" value="{{ old('phone', $business->phone) }}"></label>
                        <div class="two-col">
                            <label>Currency<input name="currency" maxlength="3" value="{{ old('currency', $business->currency) }}" required></label>
                            <label>Timezone<input name="timezone" value="{{ old('timezone', $business->timezone) }}" required></label>
                        </div>
                        <button>Save Profile</button>
                    </form>

                    <form class="panel" id="branding" method="post" action="{{ route('portal.branding.update') }}">
                        @csrf
                        <h2>Splash Page Branding</h2>
                        <label>Logo URL<input name="logo_url" type="url" value="{{ old('logo_url', $branding->logo_url) }}" placeholder="https://example.com/logo.png"></label>
                        <div class="two-col">
                            <label>Primary color<input name="primary_color" value="{{ old('primary_color', $branding->primary_color) }}" required></label>
                            <label>Accent color<input name="accent_color" value="{{ old('accent_color', $branding->accent_color) }}" required></label>
                        </div>
                        <label>Headline<input name="welcome_headline" value="{{ old('welcome_headline', $branding->welcome_headline) }}" required></label>
                        <label>Message<textarea name="welcome_message" rows="3">{{ old('welcome_message', $branding->welcome_message) }}</textarea></label>
                        <label>Terms URL<input name="terms_url" type="url" value="{{ old('terms_url', $branding->terms_url) }}"></label>
                        <button>Save Branding</button>
                    </form>

                    <section class="panel" id="packages">
                        <h2>Ad and Subscription Plans</h2>
                        <form class="inline-form plan-form" method="post" action="{{ route('portal.packages.store') }}">
                            @csrf
                            <input name="name" placeholder="Plan name" required>
                            <select name="access_type" required><option value="subscription">Subscription</option><option value="ad">Watch Ads</option></select>
                            <input name="description" placeholder="Description">
                            <input name="duration_minutes" type="number" min="5" placeholder="Access min" required>
                            <input name="ad_watch_seconds" type="number" min="5" max="600" placeholder="Ad sec">
                            <input name="price" type="number" min="0" step="0.01" placeholder="Price" required>
                            <input name="download_mbps" type="number" min="1" placeholder="Down Mbps" required>
                            <input name="upload_mbps" type="number" min="1" placeholder="Up Mbps" required>
                            <button>Add Plan</button>
                        </form>
                        <div class="table-list">
                            @foreach ($packages as $package)
                                <div>
                                    <span><strong>{{ $package->name }} ({{ $package->access_type }})</strong>{{ $package->duration_minutes }} min | {{ $business->currency }} {{ $package->price }} | {{ $package->download_mbps }}/{{ $package->upload_mbps }} Mbps @if($package->ad_watch_seconds) | {{ $package->ad_watch_seconds }} sec ad @endif</span>
                                    <form method="post" action="{{ route('portal.packages.toggle', $package) }}">@csrf<button>{{ $package->is_active ? 'Active' : 'Inactive' }}</button></form>
                                </div>
                            @endforeach
                        </div>
                    </section>

                    <section class="panel" id="routers">
                        <h2>Routers</h2>
                        <form class="inline-form" method="post" action="{{ route('portal.routers.store') }}">
                            @csrf
                            <input name="name" placeholder="Router name" required>
                            <input name="vendor" placeholder="Vendor" value="MikroTik" required>
                            <input name="mac_address" placeholder="MAC address">
                            <input name="ip_address" placeholder="IP address">
                            <button>Add Router</button>
                        </form>
                        <div class="table-list">
                            @foreach ($routers as $router)
                                <div><span><strong>{{ $router->name }}</strong>{{ $router->vendor }} | {{ $router->status }} | Secret: {{ $router->shared_secret }}</span></div>
                            @endforeach
                        </div>
                    </section>

                    <section class="panel" id="devices">
                        <h2>Device Whitelist</h2>
                        <form class="inline-form" method="post" action="{{ route('portal.devices.store') }}">
                            @csrf
                            <select name="router_id"><option value="">Any router</option>@foreach ($routers as $router)<option value="{{ $router->id }}">{{ $router->name }}</option>@endforeach</select>
                            <input name="mac_address" placeholder="Device MAC" required>
                            <input name="label" placeholder="Label">
                            <select name="status"><option value="allowed">Allowed</option><option value="blocked">Blocked</option></select>
                            <button>Save Device</button>
                        </form>
                        <div class="table-list">
                            @foreach ($devices as $device)
                                <div><span><strong>{{ $device->mac_address }}</strong>{{ $device->label ?: 'No label' }} | {{ $device->status }}</span></div>
                            @endforeach
                        </div>
                    </section>

                    <section class="panel" id="api">
                        <h2>API Connect</h2>
                        <p class="panel-copy">Use this key in the X-Portal-Key header from routers, ad systems, payment systems, captive portals, or another backend.</p>
                        <pre>API key: {{ $business->api_key }}</pre>
                        <pre>GET  /api/portal/config
GET  /api/portal/routers
POST /api/portal/router-heartbeat
POST /api/portal/sessions
POST /api/portal/ad-access
POST /api/portal/subscription-access</pre>
                        <p class="panel-copy">Guest portal URL: <a href="{{ route('guest.show') }}">/guest</a></p>
                    </section>

                    <section class="panel" id="sessions">
                        <h2>Recent Guest Sessions</h2>
                        <div class="table-list">
                            @forelse ($sessions as $session)
                                <div><span><strong>{{ $session->device_mac }}</strong>{{ $session->package?->name ?: 'No package' }} | {{ $session->access_method }} | {{ $session->status }} | expires {{ optional($session->expires_at)->diffForHumans() ?: 'not set' }}</span></div>
                            @empty
                                <p class="panel-copy">No guest sessions yet. Create one from /guest or through the API.</p>
                            @endforelse
                        </div>
                    </section>
                </section>
            </section>
        </main>
    </div>
</body>
</html>
