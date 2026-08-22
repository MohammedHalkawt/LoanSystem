<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', config('app.name')) — @yield('page')</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            background: #f5f5f7;
            color: #1d1d1f;
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
            background: rgba(255,255,255,0.92);
            border-radius: 8px;
            padding: 1.8rem;
            border: 1px solid rgba(0,0,0,0.06);
            box-shadow: 0 18px 45px rgba(0,0,0,0.04);
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
            background: #fbfbfd;
            border: 1px solid #d2d2d7;
            border-radius: 8px;
            padding: 1rem;
            text-align: left;
            cursor: pointer;
            transition: all 0.2s;
            color: inherit;
            text-decoration: none;
            display: block;
        }

        .action-btn:hover {
            background: white;
            border-color: #a1a1a6;
            transform: translateY(-1px);
        }

        .action-title {
            font-weight: 600;
            margin-bottom: 0.2rem;
        }

        .action-desc {
            font-size: 0.75rem;
            color: #6b7280;
        }

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
            @media (prefers-color-scheme: dark) {
                .nav-link {
                    color: #9ca3af;
                }
                .nav-link:hover {
                    color: white;
                    border-bottom-color: white;
                }
            }
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; background: #f5f5f7; color: #1d1d1f; }
        .navbar { background: rgba(255,255,255,0.82); backdrop-filter: blur(18px); border-bottom: 1px solid rgba(0,0,0,0.08); padding: 0.75rem 2rem; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 10; }
        .nav-brand { font-size: 1.45rem; font-weight: 700; color: #1d1d1f; background: none; -webkit-text-fill-color: currentColor; }
        .nav-links { display: flex; gap: 1.35rem; margin-left: 2rem; }
        .nav-link { color: #515154; text-decoration: none; font-size: 0.94rem; font-weight: 500; padding: 0.5rem 0; border-bottom: 2px solid transparent; }
        .nav-link:hover { color: #1d1d1f; border-bottom-color: #1d1d1f; }
        .user-menu { display: flex; align-items: center; gap: 1rem; }
        .avatar { width: 40px; height: 40px; background: #e5e7eb; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 600; color: #374151; }
        .user-info { text-align: right; }
        .user-name { font-weight: 600; font-size: 0.95rem; }
        .user-role { font-size: 0.75rem; color: #6b7280; text-transform: capitalize; }
        .logout-link { color: #6b7280; text-decoration: none; font-size: 0.9rem; padding: 0.5rem 1rem; border-radius: 20px; }
        .logout-link:hover { background: #f3f4f6; color: #1f2937; }
        .container { max-width: 1280px; margin: 0 auto; padding: 2rem; }
        .card { background: rgba(255,255,255,0.92); border-radius: 8px; padding: 1.8rem; box-shadow: 0 18px 45px rgba(0,0,0,0.04); border: 1px solid rgba(0,0,0,0.06); }
        .btn { display: inline-block; padding: 0.62rem 1.15rem; border-radius: 999px; font-size: 0.9rem; font-weight: 600; text-decoration: none; border: none; cursor: pointer; transition: all 0.18s ease; }
        .btn-primary { background: #0071e3; color: white; }
        .btn-primary:hover { background: #0077ed; transform: translateY(-1px); }
        .btn-outline { background: rgba(255,255,255,0.65); border: 1px solid #d2d2d7; color: #1d1d1f; }
        .btn-outline:hover { background: white; border-color: #a1a1a6; }
        .table { width: 100%; border-collapse: collapse; margin-top: 1rem; }
        .table th { text-align: left; padding: 0.75rem 0.5rem; font-size: 0.8rem; text-transform: uppercase; letter-spacing: 0.5px; color: #6b7280; border-bottom: 1px solid #e5e7eb; }
        .table td { padding: 1rem 0.5rem; border-bottom: 1px solid #f3f4f6; }
        .table tr:hover td { background: #f9fafb; }
        .table tbody tr[onclick] { transition: background 0.16s ease, box-shadow 0.16s ease; }
        .table tbody tr[onclick]:hover td { background: #f8fafc; }
        .table tbody tr[onclick] td:first-child { border-left: 3px solid transparent; }
        .table tbody tr[onclick]:hover td:first-child { border-left-color: #111827; }
        .form-group { margin-bottom: 1.5rem; }
        .form-group label { display: block; font-size: 0.8rem; font-weight: 600; color: #374151; margin-bottom: 0.5rem; text-transform: uppercase; letter-spacing: 0.5px; }
        .form-control { width: 100%; padding: 0.82rem 1rem; border: 1px solid #d2d2d7; border-radius: 8px; font-size: 0.95rem; background: rgba(255,255,255,0.9); }
        .form-control:focus { outline: none; border-color: #0071e3; background: white; box-shadow: 0 0 0 4px rgba(0,113,227,0.12); }
        textarea.form-control { resize: vertical; min-height: 108px; font-family: inherit; line-height: 1.5; }
        .search-box { display: flex; gap: 0.5rem; margin-bottom: 1.5rem; }
        .search-box input { flex: 1; }
        .alert { padding: 1rem 1.5rem; border-radius: 16px; margin-bottom: 1.5rem; }
        .modal-backdrop { position: fixed; inset: 0; z-index: 100; align-items: center; justify-content: center; padding: 1rem; background: rgba(29,29,31,0.34); backdrop-filter: blur(12px); }
        .modal-panel { width: min(440px, 100%); background: rgba(255,255,255,0.96); border: 1px solid rgba(0,0,0,0.08); border-radius: 8px; padding: 1.5rem; box-shadow: 0 28px 80px rgba(0,0,0,0.18); }
        .icon-close { width: 34px; height: 34px; border-radius: 999px; border: 0; background: #f5f5f7; color: #1d1d1f; font-size: 1.35rem; line-height: 1; cursor: pointer; }
        /* Dark mode */
        @media (prefers-color-scheme: dark) {
            body { background: #111827; color: #f9fafb; }
            .navbar { background: #1f2937; border-bottom-color: #374151; }
            .nav-brand { background: linear-gradient(135deg, #f3f4f6, #d1d5db); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
            .nav-link { color: #9ca3af; }
            .nav-link:hover { color: white; border-bottom-color: white; }
            .avatar { background: #4b5563; color: #f3f4f6; }
            .user-name { color: white; }
            .user-role { color: #9ca3af; }
            .logout-link { color: #9ca3af; }
            .logout-link:hover { background: #374151; color: white; }
            .card { background: #1f2937; border-color: #374151; }
            .btn-outline { border-color: #4b5563; color: #e5e7eb; }
            .btn-outline:hover { background: #374151; }
            .table th { color: #9ca3af; border-bottom-color: #374151; }
            .table td { border-bottom-color: #374151; }
            .table tr:hover td { background: #26313d; }
            .table tbody tr[onclick]:hover td:first-child { border-left-color: #f9fafb; }
            .form-group label { color: #e5e7eb; }
            .form-control { background: #111827; border-color: #374151; color: white; }
            .form-control:focus { border-color: #9ca3af; background: #1f2937; }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <span class="nav-brand">ShowRoom</span>
        <div class="nav-links">
            <a href="{{ route('dashboard') }}" class="nav-link">Dashboard</a>
            <a href="{{ route('customers.index') }}" class="nav-link">Customers</a>
            <a href="{{ route('purchases.index') }}" class="nav-link">Purchases</a>
            <a href="{{ route('rents.index') }}" class="nav-link">Rent</a>
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
        @yield('content')
    </div>
</body>
</html>
