<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Nextgen Technology Login</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="auth-body">
    <main class="auth-page">
        <section class="auth-card">
            <div class="guest-brand">
                <div class="brand-mark"><span></span></div>
                <div>
                    <strong>Nextgen Technology</strong>
                    <span>WiFi Access Administration</span>
                </div>
            </div>

            <h1>Sign in</h1>
            <p>Use your operator account to manage splash pages, plans, routers, devices, and API access.</p>

            @if ($errors->any())
                <div class="flash error">
                    @foreach ($errors->all() as $error)
                        <div>{{ $error }}</div>
                    @endforeach
                </div>
            @endif

            <form method="post" action="{{ route('login.store') }}">
                @csrf
                <label>Email<input name="email" type="email" value="{{ old('email') }}" required autofocus></label>
                <label>Password<input name="password" type="password" required></label>
                <label class="check-row"><input name="remember" type="checkbox" value="1"> Remember this device</label>
                <button>Sign In</button>
            </form>
        </section>
    </main>
</body>
</html>
