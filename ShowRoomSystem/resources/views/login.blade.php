<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} — Login</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: linear-gradient(145deg, #f5f7fa 0%, #e9edf2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        .login-card {
            max-width: 440px;
            width: 100%;
            background: white;
            border-radius: 32px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15);
            padding: 2.5rem 2rem;
            transition: transform 0.2s ease;
        }

        .login-card:hover {
            transform: translateY(-4px);
        }

        .app-name {
            text-align: center;
            margin-bottom: 2rem;
        }

        .app-name h1 {
            font-size: 2rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1a1e24, #2d3748);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 0.25rem;
        }

        .app-name p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #374151;
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        input {
            width: 100%;
            padding: 0.9rem 1.2rem;
            border: 2px solid #e5e7eb;
            border-radius: 16px;
            font-size: 0.95rem;
            transition: all 0.2s;
            background: #fafafa;
        }

        input:focus {
            outline: none;
            border-color: #2d3748;
            background: white;
            box-shadow: 0 0 0 4px rgba(45, 55, 72, 0.1);
        }

        .forgot-link {
            text-align: right;
            margin-top: 0.5rem;
        }

        .forgot-link a {
            color: #6b7280;
            font-size: 0.8rem;
            text-decoration: none;
            border-bottom: 1px dashed #d1d5db;
        }

        .forgot-link a:hover {
            color: #1f2937;
            border-bottom-color: #1f2937;
        }

        button {
            width: 100%;
            padding: 0.9rem;
            background: #1a1e24;
            color: white;
            border: none;
            border-radius: 16px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            margin-top: 0.5rem;
        }

        button:hover {
            background: #0f1318;
            transform: scale(1.02);
            box-shadow: 0 10px 20px -10px rgba(0, 0, 0, 0.2);
        }

        .error-box {
            margin-top: 1.5rem;
            padding: 1rem;
            background: #fff5f5;
            border: 1px solid #feb2b2;
            border-radius: 16px;
            color: #c53030;
            font-size: 0.9rem;
            text-align: center;
        }

        .footer {
            margin-top: 2.5rem;
            text-align: center;
            color: #9ca3af;
            font-size: 0.75rem;
        }

        @media (prefers-color-scheme: dark) {
            body {
                background: linear-gradient(145deg, #0b0e11, #1a1e24);
            }
            .login-card {
                background: #1f2937;
                box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            }
            .app-name h1 {
                background: linear-gradient(135deg, #f3f4f6, #d1d5db);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .app-name p {
                color: #9ca3af;
            }
            label {
                color: #e5e7eb;
            }
            input {
                background: #111827;
                border-color: #374151;
                color: white;
            }
            input:focus {
                border-color: #9ca3af;
                background: #1f2937;
            }
            .forgot-link a {
                color: #9ca3af;
            }
            button {
                background: #f3f4f6;
                color: #111827;
            }
            button:hover {
                background: white;
            }
            .error-box {
                background: #3b1818;
                border-color: #742a2a;
                color: #feb2b2;
            }
        }
    </style>
</head>
<body>
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