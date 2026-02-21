<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} — Login</title>
    @vite('resources/css/app.css')

</head>
<body class="login-page">
    <div class="login-card">
        <div class="app-name">
            <h1>{{ config('app.name', 'Laravel') }}</h1>
            <p>Secure access</p>
        </div>

        <form method="POST" action="/login">
            @csrf
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" placeholder="you@example.com" required autofocus>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" placeholder="••••••••" required>
                <div class="forgot-link">
                    <a href="#">Forgot password?</a>
                </div>
            </div>

            <button type="submit">Sign in</button>
        </form>

        @if(session('error'))
            <div class="error-box">
                {{ session('error') }}
            </div>
        @endif

        <div class="footer">
            &copy; {{ date('Y') }} {{ config('app.name', 'Laravel') }}. All rights reserved.
        </div>
    </div>
</body>
</html>