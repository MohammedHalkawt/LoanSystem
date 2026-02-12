<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ config('app.name', 'Laravel') }} — Dashboard</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f9fafb;
            color: #1f2937;
            line-height: 1.5;
        }

        .navbar {
            background: white;
            border-bottom: 1px solid #e5e7eb;
            padding: 0.75rem 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 10;
        }

        .nav-brand {
            font-size: 1.5rem;
            font-weight: 700;
            background: linear-gradient(135deg, #1a1e24, #2d3748);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 1.5rem;
            margin-left: 2rem;
        }

        .nav-link {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.95rem;
            font-weight: 500;
            padding: 0.5rem 0;
            border-bottom: 2px solid transparent;
            transition: all 0.2s;
        }

        .nav-link:hover {
            color: #1f2937;
            border-bottom-color: #1f2937;
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        .avatar {
            width: 40px;
            height: 40px;
            background: #e5e7eb;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            color: #374151;
            text-transform: uppercase;
        }

        .user-info {
            text-align: right;
        }

        .user-name {
            font-weight: 600;
            font-size: 0.95rem;
        }

        .user-role {
            font-size: 0.75rem;
            color: #6b7280;
            text-transform: capitalize;
        }

        .logout-link {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: background 0.2s;
        }

        .logout-link:hover {
            background: #f3f4f6;
            color: #1f2937;
        }

        .container {
            max-width: 1280px;
            margin: 0 auto;
            padding: 2rem 2rem;
        }

        .toast-success {
            background: #ecfdf5;
            border: 1px solid #a7f3d0;
            color: #065f46;
            padding: 1rem 1.5rem;
            border-radius: 16px;
            margin-bottom: 2rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .toast-success button {
            background: none;
            border: none;
            color: #065f46;
            font-size: 1.2rem;
            cursor: pointer;
            padding: 0 0.5rem;
        }

        .welcome-header {
            margin-bottom: 2rem;
        }

        .welcome-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #111827;
            margin-bottom: 0.25rem;
        }

        .welcome-header p {
            color: #6b7280;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2.5rem;
        }

        .stat-card {
            background: white;
            border-radius: 24px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.02), 0 1px 2px rgba(0,0,0,0.03);
            border: 1px solid #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: space-between;
            transition: all 0.2s;
        }

        .stat-card:hover {
            box-shadow: 0 10px 25px -5px rgba(0,0,0,0.05);
            border-color: #e5e7eb;
        }

        .stat-content {
            flex: 1;
        }

        .stat-label {
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #6b7280;
            margin-bottom: 0.25rem;
        }

        .stat-value {
            font-size: 1.8rem;
            font-weight: 700;
            color: #111827;
            line-height: 1.2;
        }

        .stat-unit {
            font-size: 0.9rem;
            color: #9ca3af;
            margin-left: 0.25rem;
        }

        .stat-icon {
            width: 48px;
            height: 48px;
            background: #f3f4f6;
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #4b5563;
        }

        .quick-actions {
            background: white;
            border-radius: 24px;
            padding: 1.8rem;
            border: 1px solid #f3f4f6;
        }

        .quick-actions h3 {
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            color: #1f2937;
        }

        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
        }

        .action-btn {
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 1rem;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s;
            color: inherit;
            text-decoration: none;
            display: block;
        }

        .action-btn:hover {
            background: #f3f4f6;
            border-color: #d1d5db;
            transform: translateY(-2px);
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .action-desc {
            font-size: 0.75rem;
            color: #6b7280;
        }

        /* ===== DARK MODE ===== */
        @media (prefers-color-scheme: dark) {
            body {
                background: #111827;
                color: #f9fafb;
            }
            .navbar {
                background: #1f2937;
                border-bottom-color: #374151;
            }
            .nav-brand {
                background: linear-gradient(135deg, #f3f4f6, #d1d5db);
                -webkit-background-clip: text;
                -webkit-text-fill-color: transparent;
            }
            .nav-link {
                color: #9ca3af;
            }
            .nav-link:hover {
                color: white;
                border-bottom-color: white;
            }
            .avatar {
                background: #4b5563;
                color: #f3f4f6;
            }
            .user-name {
                color: white;
            }
            .user-role {
                color: #9ca3af;
            }
            .logout-link {
                color: #9ca3af;
            }
            .logout-link:hover {
                background: #374151;
                color: white;
            }
            .stat-card {
                background: #1f2937;
                border-color: #374151;
            }
            .stat-label {
                color: #9ca3af;
            }
            .stat-value {
                color: white;
            }
            .stat-icon {
                background: #374151;
                color: #e5e7eb;
            }
            .quick-actions {
                background: #1f2937;
                border-color: #374151;
            }
            .quick-actions h3 {
                color: white;
            }
            .action-btn {
                background: #111827;
                border-color: #374151;
                color: #f3f4f6;
            }
            .action-btn:hover {
                background: #1f2937;
                border-color: #4b5563;
            }
            .action-desc {
                color: #9ca3af;
            }
            .toast-success {
                background: #064e3b;
                border-color: #10b981;
                color: #d1fae5;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <span class="nav-brand">{{ config('app.name', 'Laravel') }}</span>
        <div class="nav-links">
            <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
            <a href="{{ route('customers.index') }}" class="nav-link">Customers</a>
        </div>
        <div class="user-menu">
            <div class="user-info">
                <div class="user-name">{{ session('user_name') }}</div>
                <div class="user-role">{{ session('user_role') }}</div>
            </div>
            <div class="avatar">{{ substr(session('user_name'), 0, 1) }}</div>
            <a href="/logout" class="logout-link">Logout</a>
        </div>
    </nav>

    <div class="container">
        @if(session('success'))
            <div class="toast-success">
                <span>{{ session('success') }}</span>
                <button onclick="this.parentElement.remove()">✕</button>
            </div>
        @endif

        <div class="welcome-header">
            <h2>Welcome back, {{ session('user_name') }}! 👋</h2>
            <p>Here's your account overview</p>
        </div>

        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-label">User ID</div>
                    <div class="stat-value">{{ session('user_id') }}</div>
                </div>
                <div class="stat-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                    </svg>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-label">Role</div>
                    <div class="stat-value" style="text-transform: capitalize;">{{ session('user_role') }}</div>
                </div>
                <div class="stat-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-label">Email</div>
                    <div class="stat-value" style="font-size: 1.2rem;">{{ session('user_email') }}</div>
                </div>
                <div class="stat-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-content">
                    <div class="stat-label">Member since</div>
                    <div class="stat-value" style="font-size: 1.2rem;">{{ session('user_created_at') ?? 'N/A' }}</div>
                </div>
                <div class="stat-icon">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            </div>
        </div>

        <div class="quick-actions">
            <h3>Quick actions</h3>
            <div class="actions-grid">
                <a href="#" class="action-btn">
                    <div class="action-title">View profile</div>
                    <div class="action-desc">Edit your details</div>
                </a>
                <a href="#" class="action-btn">
                    <div class="action-title">Settings</div>
                    <div class="action-desc">Account preferences</div>
                </a>
                <a href="#" class="action-btn">
                    <div class="action-title">Security</div>
                    <div class="action-desc">Change password</div>
                </a>
                 <a href="{{ route('customers.index') }}" class="action-btn">
                    <div class="action-title">Customers</div>
                    <div class="action-desc">Manage your customers</div>
                </a>
                <a href="/logout" class="action-btn">
                    <div class="action-title" style="color: #dc2626;">Logout</div>
                    <div class="action-desc">End your session</div>
                </a>
            </div>
        </div>
    </div>
</body>
</html>