<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $business->company_name }} Guest WiFi</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="guest-body">
    <main class="guest-page" style="--guest-primary: {{ $branding->primary_color }}; --guest-accent: {{ $branding->accent_color }};">
        <section class="guest-hero">
            <div class="guest-brand">
                <div class="brand-mark"><span></span></div>
                <div>
                    <strong>{{ $business->company_name }}</strong>
                    <span>{{ $business->site_name }} WiFi Access</span>
                </div>
            </div>

            <div class="guest-copy">
                <h1>{{ $branding->welcome_headline }}</h1>
                <p>{{ $branding->welcome_message }}</p>
            </div>
        </section>

        <section class="access-panel">
            @if (session('guest_status'))
                <div class="flash">{{ session('guest_status') }}</div>
            @endif

            @if ($errors->any())
                <div class="flash error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            @unless ($guest)
                <article class="access-card guest-login-card">
                    <h2>Guest login</h2>
                    <p>Sign in with your device details. After login you can watch ads for 10 minutes or choose a subscription plan.</p>
                    <form method="post" action="{{ route('guest.login') }}">
                        @csrf
                        <input name="guest_name" placeholder="Full name" required>
                        <input name="phone" placeholder="Phone number">
                        <input name="email" type="email" placeholder="Email address">
                        <input name="device_mac" placeholder="Device MAC address" required>
                        <button>Continue</button>
                    </form>
                </article>
            @else
                <div class="guest-session-bar">
                    <span>Signed in as <strong>{{ $guest['guest_name'] }}</strong> on device <strong>{{ $guest['device_mac'] }}</strong></span>
                    <form method="post" action="{{ route('guest.logout') }}">@csrf<button>Change Device</button></form>
                </div>

                <div class="access-grid">
                    <article class="access-card">
                        <h2>Watch ads</h2>
                        <p>Watch a sponsored ad and receive {{ $adPackage?->duration_minutes ?? 10 }} minutes of guest internet access.</p>
                        <form method="post" action="{{ route('guest.ad') }}">
                            @csrf
                            <button>Watch Ad and Connect</button>
                        </form>
                    </article>

                    <article class="access-card premium">
                        <h2>Subscription plan</h2>
                        <p>Choose a paid plan for longer access, faster speed, and fewer interruptions.</p>
                        <form method="post" action="{{ route('guest.subscription') }}">
                            @csrf
                            <select name="package_id" required>
                                @foreach ($subscriptionPackages as $package)
                                    <option value="{{ $package->id }}">
                                        {{ $package->name }} - {{ $business->currency }} {{ $package->price }} / {{ $package->duration_minutes }} min
                                    </option>
                                @endforeach
                            </select>
                            <input name="payment_reference" placeholder="Payment reference (manual/gateway)">
                            <button>Subscribe and Connect</button>
                        </form>
                    </article>
                </div>

                <div class="plan-strip">
                    @foreach ($subscriptionPackages as $package)
                        <div>
                            <strong>{{ $package->name }}</strong>
                            <span>{{ $business->currency }} {{ $package->price }} | {{ $package->duration_minutes }} min | {{ $package->download_mbps }}/{{ $package->upload_mbps }} Mbps</span>
                        </div>
                    @endforeach
                </div>
            @endunless
        </section>
    </main>
</body>
</html>
