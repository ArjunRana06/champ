<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Dashboard</title>

    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#6366f1">

    <style>
        :root {
            --bg-body: #f8fafc;
            --text-primary: #1e1b4b;
            --text-secondary: #6b7280;
            --text-muted: #9ca3af;
            --glass-bg: rgba(255,255,255,0.6);
            --glass-bg-hover: rgba(255,255,255,0.75);
            --glass-border: rgba(255,255,255,0.5);
            --card-shadow: 0 25px 50px -12px rgba(0,0,0,0.08);
            --navbar-bg: rgba(255,255,255,0.6);
            --footer-bg: rgba(255,255,255,0.5);
            --input-bg: white;
            --input-border: #e5e7eb;
            --table-row-hover: rgba(99,102,241,0.03);
            --table-header-bg: rgba(99,102,241,0.04);
            --badge-bg: #eef2ff;
            --badge-color: #6366f1;
            --stat-up-bg: #ecfdf5;
            --stat-up-color: #059669;
            --stat-down-bg: #fef2f2;
            --stat-down-color: #dc2626;
            --card-accent: #6366f1;
            --link-color: #6366f1;
            --divider-color: rgba(0,0,0,0.06);
            --dropdown-bg: rgba(255,255,255,0.85);
        }
        [data-theme="dark"] {
            --bg-body: #0b1120;
            --text-primary: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --glass-bg: rgba(15,23,42,0.75);
            --glass-bg-hover: rgba(15,23,42,0.85);
            --glass-border: rgba(51,65,85,0.6);
            --card-shadow: 0 25px 50px -12px rgba(0,0,0,0.5);
            --navbar-bg: rgba(11,17,32,0.8);
            --footer-bg: rgba(11,17,32,0.6);
            --input-bg: #1e293b;
            --input-border: #334155;
            --table-row-hover: rgba(99,102,241,0.12);
            --table-header-bg: rgba(99,102,241,0.08);
            --badge-bg: rgba(99,102,241,0.15);
            --badge-color: #a5b4fc;
            --stat-up-bg: rgba(16,185,129,0.15);
            --stat-up-color: #6ee7b7;
            --stat-down-bg: rgba(239,68,68,0.15);
            --stat-down-color: #fca5a5;
            --card-accent: #818cf8;
            --link-color: #818cf8;
            --divider-color: rgba(255,255,255,0.06);
            --dropdown-bg: rgba(15,23,42,0.9);
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-body);
            color: var(--text-primary);
            overflow-x: hidden;
            position: relative;
            min-height: 100vh;
            transition: background 0.3s, color 0.3s;
        }
        .mesh-bg {
            position: fixed; inset: 0; z-index: 0;
            background:
                radial-gradient(ellipse 80% 60% at 0% 20%, rgba(99,102,241,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 100% 0%, rgba(139,92,246,0.1) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 30% 60%, rgba(6,182,212,0.06) 0%, transparent 50%);
            animation: meshShift 20s ease-in-out infinite alternate;
            transition: opacity 0.3s;
        }
        [data-theme="dark"] .mesh-bg { opacity: 0.3; }
        @keyframes meshShift {
            0% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.05); opacity: 1; }
        }
        .orb {
            position: fixed; border-radius: 50%; filter: blur(100px); pointer-events: none; z-index: 0;
            animation: orbFloat 25s ease-in-out infinite alternate;
        }
        .orb:nth-child(1) { width: 500px; height: 500px; background: rgba(99,102,241,0.15); top: -10%; left: -10%; }
        .orb:nth-child(2) { width: 400px; height: 400px; background: rgba(139,92,246,0.1); bottom: -5%; right: -5%; animation-delay: -8s; }
        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, -40px) scale(1.1); }
        }

        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* SIDEBAR — matches login hero panel */
        .sidebar {
            width: 270px;
            min-width: 270px;
            background: linear-gradient(135deg, #1e1b4b, #312e81, #581c87);
            color: white;
            display: flex;
            transition: transform 0.3s ease;
            flex-direction: column;
            position: sticky;
            top: 0;
            height: 100vh;
            z-index: 20;
            overflow-y: auto;
            overflow-x: hidden;
        }
        .sidebar::before {
            content: '';
            position: absolute; inset: 0;
            background: repeating-linear-gradient(45deg, rgba(255,255,255,0.03) 0px, rgba(255,255,255,0.03) 2px, transparent 2px, transparent 8px);
            pointer-events: none;
        }
        .sidebar::after {
            content: '';
            position: absolute; inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% 0%, rgba(255,255,255,0.06) 0%, transparent 60%);
            pointer-events: none;
        }
        .sidebar::-webkit-scrollbar { width: 4px; }
        .sidebar::-webkit-scrollbar-track { background: transparent; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 4px; }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 1.8rem 1.5rem;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            position: relative; z-index: 1;
        }
        .sidebar-brand .brand-icon {
            font-size: 1.6rem;
            filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2));
        }
        .sidebar-brand .brand-text {
            font-weight: 700;
            font-size: 1.2rem;
        }
        .sidebar-brand .brand-badge {
            font-size: 0.55rem; text-transform: uppercase; letter-spacing: 0.08em;
            background: rgba(255,255,255,0.1); color: #c4b5fd;
            padding: 0.15rem 0.5rem; border-radius: 20px;
            margin-left: auto;
        }

        .sidebar-nav {
            padding: 0.75rem;
            flex: 1;
            position: relative; z-index: 1;
        }
        .sidebar-nav .nav-section-label {
            font-size: 0.6rem;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            color: rgba(255,255,255,0.35);
            padding: 1.25rem 0.9rem 0.4rem;
            font-weight: 600;
        }
        .sidebar-nav .nav-item { margin-bottom: 2px; }
        .sidebar-nav .nav-link {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.7rem 0.9rem;
            color: rgba(255,255,255,0.65);
            border-radius: 0.75rem;
            font-size: 0.88rem;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .sidebar-nav .nav-link i {
            font-size: 1.2rem;
            width: 24px;
            text-align: center;
            flex-shrink: 0;
        }
        .sidebar-nav .nav-link:hover {
            background: rgba(255,255,255,0.08);
            color: white;
        }
        .sidebar-nav .nav-link.active {
            background: rgba(255,255,255,0.12);
            color: white;
            box-shadow: inset 3px 0 0 #c4b5fd;
        }
        .sidebar-nav .nav-link .chevron {
            margin-left: auto;
            font-size: 0.65rem;
            transition: transform 0.2s;
        }
        .sidebar-nav .nav-link[aria-expanded="true"] .chevron {
            transform: rotate(180deg);
        }
        .sidebar-nav .submenu {
            padding-left: 2.5rem;
            margin-top: 2px;
        }
        .sidebar-nav .submenu .nav-link {
            font-size: 0.82rem;
            padding: 0.45rem 0.9rem;
        }

        .sidebar-footer {
            padding: 1rem 1.25rem;
            border-top: 1px solid rgba(255,255,255,0.08);
            display: flex;
            align-items: center;
            gap: 0.75rem;
            position: relative; z-index: 1;
        }
        .sidebar-footer .mini-avatar {
            width: 34px; height: 34px;
            background: rgba(255,255,255,0.15);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 0.8rem;
        }
        .sidebar-footer .sidebar-user-info { flex: 1; min-width: 0; }
        .sidebar-footer .sidebar-user-info .name {
            font-size: 0.82rem; font-weight: 600; color: rgba(255,255,255,0.85);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sidebar-footer .sidebar-user-info .role {
            font-size: 0.65rem; color: rgba(255,255,255,0.4);
        }
        .sidebar-footer a {
            color: rgba(255,255,255,0.4);
            font-size: 1rem;
            transition: color 0.2s;
        }
        .sidebar-footer a:hover { color: white; }

        /* MAIN CONTENT */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            min-width: 0;
        }

        /* NAVBAR */
        .navbar-top {
            background: var(--navbar-bg);
            backdrop-filter: blur(20px) saturate(1.8);
            border-bottom: 1px solid var(--glass-border);
            padding: 0.75rem 1.5rem;
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 15;
            gap: 1rem;
        }

        .navbar-left {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .sidebar-toggle-btn {
            background: none; border: none; color: #6366f1;
            font-size: 1.4rem; cursor: pointer; padding: 0.25rem;
            display: none; line-height: 1;
        }

        .search-box {
            position: relative;
            width: 300px;
        }
        .search-box i {
            position: absolute;
            left: 16px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 0.9rem;
        }
        .search-box input {
            width: 100%;
            padding: 0.6rem 1rem 0.6rem 2.6rem;
            border: 1.5px solid var(--input-border);
            border-radius: 40px;
            background: var(--input-bg);
            color: var(--text-primary);
            font-size: 0.88rem;
            font-family: 'Inter', sans-serif;
            transition: all 0.2s;
        }
        .search-box input::placeholder { color: var(--text-muted); }
        .search-box input:focus {
            outline: none;
            border-color: var(--card-accent);
            box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }

        .navbar-right {
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .nav-icon-btn {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            color: var(--card-accent);
            display: flex; align-items: center; justify-content: center;
            cursor: pointer; transition: all 0.2s; font-size: 1rem;
            position: relative; text-decoration: none;
        }
        .nav-icon-btn:hover {
            border-color: var(--card-accent);
            background: var(--badge-bg);
            transform: scale(1.05);
        }
        .nav-icon-btn .badge-dot {
            position: absolute; top: 4px; right: 4px;
            width: 8px; height: 8px; border-radius: 50%;
            background: #ef4444;
            border: 2px solid white;
        }

        .user-dropdown-btn {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            padding: 0.3rem 0.3rem 0.3rem 0.8rem;
            background: var(--input-bg);
            border: 1.5px solid var(--input-border);
            border-radius: 40px;
            cursor: pointer;
            transition: all 0.2s;
            color: var(--text-primary);
            text-decoration: none;
        }
        .user-dropdown-btn:hover {
            border-color: var(--card-accent);
            box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }
        .user-dropdown-btn .user-avatar-small {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-weight: 600; font-size: 0.8rem;
        }
        .user-dropdown-btn .user-name {
            font-size: 0.85rem; font-weight: 500;
            color: var(--text-primary);
        }

        /* Dropdown */
        .dropdown-menu {
            background: var(--dropdown-bg);
            backdrop-filter: blur(20px) saturate(1.8);
            border: 1px solid var(--glass-border);
            border-radius: 1.2rem;
            padding: 0.5rem;
            box-shadow: var(--card-shadow);
        }
        .dropdown-menu .dropdown-item {
            border-radius: 0.7rem;
            padding: 0.5rem 0.9rem;
            font-size: 0.85rem;
            color: var(--text-primary);
            transition: all 0.15s;
        }
        .dropdown-menu .dropdown-item:hover {
            background: var(--badge-bg);
            color: var(--card-accent);
        }
        .dropdown-menu .dropdown-item i { width: 20px; text-align: center; margin-right: 0.5rem; }
        .dropdown-menu .dropdown-divider { border-color: var(--divider-color); }

        /* MAIN PADDING */
        main { padding: 1.5rem; flex: 1; }

        /* Toast container */
        #toast-container {
            position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 9999;
            display: flex; flex-direction: column; gap: 0.5rem;
            pointer-events: none;
        }
        #toast-container .toast-item {
            pointer-events: auto;
            padding: 0.85rem 1.2rem;
            border-radius: 1rem;
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(1.8);
            border: 1px solid var(--glass-border);
            box-shadow: var(--card-shadow);
            font-size: 0.88rem;
            color: var(--text-primary);
            display: flex; align-items: center; gap: 0.75rem;
            min-width: 300px;
            max-width: 450px;
            animation: toastIn 0.3s ease-out;
            transition: opacity 0.3s, transform 0.3s;
        }
        #toast-container .toast-item.removing {
            opacity: 0; transform: translateX(100px);
        }
        @keyframes toastIn {
            from { opacity: 0; transform: translateX(100px); }
            to { opacity: 1; transform: translateX(0); }
        }
        #toast-container .toast-item .toast-icon { font-size: 1.1rem; flex-shrink: 0; }
        #toast-container .toast-item .toast-msg { flex: 1; }

        /* FOOTER */
        .dashboard-footer {
            background: var(--footer-bg);
            backdrop-filter: blur(12px) saturate(1.8);
            border-top: 1px solid var(--glass-border);
            padding: 1rem 1.5rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.82rem;
            color: var(--text-secondary);
        }
        .dashboard-footer a {
            color: var(--text-secondary);
            text-decoration: none;
            margin-left: 1.25rem;
            transition: color 0.2s;
        }
        .dashboard-footer a:hover { color: var(--card-accent); }
        .dashboard-footer .footer-links { display: flex; align-items: center; gap: 0.25rem; }

        /* RESPONSIVE */
        @media (max-width: 992px) {
            .sidebar {
                position: fixed;
                left: 0;
                top: 0;
                height: 100vh;
                z-index: 1050;
                transform: translateX(-100%);
                transition: transform 0.3s ease;
            }
            .sidebar.show { transform: translateX(0); }
            .sidebar-toggle-btn { display: block; }
            .search-box { width: 200px; }
        }
        @media (max-width: 576px) {
            .search-box { display: none; }
            .user-dropdown-btn .user-name { display: none; }
        }

        ::-webkit-scrollbar { width: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; border-radius: 10px; }
        ::-webkit-scrollbar-thumb { background: #c7d2fe; border-radius: 10px; }
        ::-webkit-scrollbar-thumb:hover { background: #a5b4fc; }
        [data-theme="dark"] ::-webkit-scrollbar-track { background: #1e293b; }
        [data-theme="dark"] ::-webkit-scrollbar-thumb { background: #475569; }
        [data-theme="dark"] ::-webkit-scrollbar-thumb:hover { background: #64748b; }

        /* GLASS CARD — matches login page glass */
        .glass-card {
            background: var(--glass-bg);
            backdrop-filter: blur(20px) saturate(1.8);
            border: 1px solid var(--glass-border);
            border-radius: 1.5rem;
            padding: 1.25rem;
            transition: all 0.3s ease;
            box-shadow: var(--card-shadow);
        }
        .glass-card:hover {
            background: var(--glass-bg-hover);
            box-shadow: var(--card-shadow);
        }

        .glass-table {
            width: 100%; border-collapse: separate; border-spacing: 0;
        }
        .glass-table thead th {
            background: var(--table-header-bg);
            color: var(--card-accent);
            font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.05em;
            padding: 0.85rem 1rem; border-bottom: 1.5px solid var(--glass-border);
            font-weight: 600;
        }
        .glass-table tbody td {
            padding: 0.85rem 1rem;
            border-bottom: 1px solid var(--glass-border);
            color: var(--text-primary);
            font-size: 0.85rem;
        }
        .glass-table tbody tr:last-child td { border-bottom: none; }
        .glass-table tbody tr:hover td { background: var(--table-row-hover); }

        /* DARK BUTTON — matches login btn */
        .dark-btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.65rem 1.5rem; font-size: 0.88rem; font-weight: 600;
            border: none; border-radius: 9999px; cursor: pointer;
            font-family: 'Inter', sans-serif;
            background: #111827; color: white;
            transition: all 0.25s; box-shadow: 0 4px 14px rgba(0,0,0,0.08);
            text-decoration: none;
        }
        [data-theme="dark"] .dark-btn {
            background: #1e293b;
            color: #f1f5f9;
        }
        .dark-btn:hover {
            background: #1f2937;
            transform: translateY(-1px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.12);
            color: white;
        }
        [data-theme="dark"] .dark-btn:hover {
            background: #334155;
            color: #f8fafc;
        }

        [data-theme="dark"] .form-glass { background: rgba(15,23,42,0.6); }
        [data-theme="dark"] .form-glass .form-label { color: var(--card-accent); }
        [data-theme="dark"] .form-glass .form-control,
        [data-theme="dark"] .form-glass .form-select {
            background: var(--input-bg);
            border-color: var(--input-border);
            color: var(--text-primary);
        }
        [data-theme="dark"] .form-glass .form-control:focus,
        [data-theme="dark"] .form-glass .form-select:focus {
            border-color: var(--card-accent);
            box-shadow: 0 0 0 4px rgba(129,140,248,0.15);
        }
        [data-theme="dark"] .form-glass .form-control::placeholder { color: var(--text-muted); }
        [data-theme="dark"] .form-glass .input-group-text {
            background: #1e293b;
            border-color: var(--input-border);
            color: var(--text-primary);
        }

        [data-theme="dark"] .pagination-glass .page-link {
            background: var(--input-bg);
            border-color: var(--input-border);
            color: var(--text-primary);
        }
        [data-theme="dark"] .pagination-glass .page-link:hover {
            background: var(--badge-bg);
            border-color: var(--card-accent);
            color: var(--card-accent);
        }
        [data-theme="dark"] .pagination-glass .page-item.active .page-link {
            background: var(--card-accent);
            border-color: var(--card-accent);
            color: #fff;
        }

        [data-theme="dark"] .option-label { background: var(--input-bg) !important; border-color: var(--input-border) !important; color: var(--text-primary); }
        [data-theme="dark"] .tf-label { background: var(--input-bg) !important; border-color: var(--input-border) !important; color: var(--text-primary); }
        [data-theme="dark"] .tf-btn { background: var(--input-bg) !important; border-color: var(--input-border) !important; color: var(--text-primary) !important; }
        [data-theme="dark"] .explanation-box { background: rgba(16,185,129,0.1) !important; border-color: rgba(16,185,129,0.3) !important; }
        [data-theme="dark"] .page-description { color: var(--text-secondary); }

        .dark-btn-outline {
            background: transparent;
            border: 1.5px solid var(--input-border);
            color: var(--text-primary);
            box-shadow: none;
        }
        .dark-btn-outline:hover {
            background: var(--badge-bg);
            border-color: var(--card-accent);
            color: var(--card-accent);
            box-shadow: none;
        }

        .btn-soft {
            display: inline-flex; align-items: center; gap: 0.4rem;
            padding: 0.45rem 1rem; font-size: 0.82rem; font-weight: 500;
            border: 1.5px solid var(--input-border); border-radius: 40px;
            background: var(--input-bg); color: var(--text-primary);
            cursor: pointer; transition: all 0.2s; text-decoration: none;
            font-family: 'Inter', sans-serif;
        }
        .btn-soft:hover {
            border-color: var(--card-accent);
            color: var(--card-accent);
            background: var(--badge-bg);
        }
        .btn-soft.danger:hover { border-color: #ef4444; color: #ef4444; background: rgba(239,68,68,0.1); }
        .btn-soft.success:hover { border-color: #10b981; color: #10b981; background: rgba(16,185,129,0.1); }
        .btn-soft.warning:hover { border-color: #f59e0b; color: #f59e0b; background: rgba(245,158,11,0.1); }

        .stat-badge {
            display: inline-flex; align-items: center; gap: 0.25rem;
            padding: 0.15rem 0.6rem; border-radius: 40px;
            font-size: 0.7rem; font-weight: 600;
        }
        .stat-badge.up { background: var(--stat-up-bg); color: var(--stat-up-color); }
        .stat-badge.down { background: var(--stat-down-bg); color: var(--stat-down-color); }

        .page-header {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem;
        }
        .page-header h2 {
            font-weight: 800; font-size: 1.5rem;
            color: var(--text-primary);
        }
        .page-header p {
            color: var(--text-secondary); font-size: 0.88rem; margin: 0.2rem 0 0;
        }

        .form-glass {
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(4px);
            border-radius: 1.5rem;
            padding: 1.5rem;
        }
        .form-glass .form-label {
            font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.05em; color: #6366f1; margin-bottom: 0.4rem;
        }
        .form-glass .form-control, .form-glass .form-select {
            background: white;
            border: 1.5px solid #e5e7eb;
            border-radius: 1rem;
            color: #111827;
            padding: 0.7rem 1.1rem;
            font-size: 0.9rem;
            font-family: 'Inter', sans-serif;
        }
        .form-glass .form-control:focus, .form-glass .form-select:focus {
            outline: none; border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }
        .form-glass .form-control::placeholder { color: #9ca3af; }
        .form-glass textarea.form-control { resize: vertical; min-height: 100px; }
        .form-glass .input-group-text {
            background: #f8fafc;
            border: 1.5px solid #e5e7eb;
            color: #6366f1;
            border-radius: 1rem 0 0 1rem;
            font-size: 0.9rem;
        }

        .pagination-glass .page-link {
            background: white;
            border: 1.5px solid #e5e7eb;
            color: #374151;
            border-radius: 0.6rem;
            margin: 0 2px;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
        }
        .pagination-glass .page-link:hover {
            background: #eef2ff;
            border-color: #6366f1;
            color: #6366f1;
        }
        .pagination-glass .page-item.active .page-link {
            background: #111827;
            border-color: #111827;
            color: white;
        }
        .pagination-glass .page-item.disabled .page-link { opacity: 0.4; }

        .page-description { color: #6b7280; font-size: 0.88rem; margin-bottom: 1.5rem; }
    </style>
    @stack('styles')
</head>
<body>
    <div class="orb"></div><div class="orb"></div>
    <div class="mesh-bg"></div>

    <div class="dashboard-wrapper">
        @include('Backend.sidebar')
        <div class="main-content">
            @include('Backend.navbar')
            <main>
                @yield('content')
            </main>
            @include('Backend.footer')
        </div>
    </div>

    <div id="toast-container"></div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function showToast(message, type) {
            type = type || 'info';
            const icons = { success: 'bi-check-circle-fill', error: 'bi-exclamation-circle-fill', warning: 'bi-exclamation-triangle-fill', info: 'bi-info-circle-fill' };
            const colors = { success: '#059669', error: '#dc2626', warning: '#d97706', info: '#6366f1' };
            const container = document.getElementById('toast-container');
            const toast = document.createElement('div');
            toast.className = 'toast-item';
            toast.innerHTML = '<span class="toast-icon" style="color:' + (colors[type] || colors.info) + ';"><i class="bi ' + (icons[type] || icons.info) + '"></i></span><span class="toast-msg">' + message + '</span><button onclick="this.closest(\'.toast-item\').classList.add(\'removing\');setTimeout(()=>this.closest(\'.toast-item\').remove(),300)" style="background:none;border:none;color:var(--text-muted);cursor:pointer;padding:0;font-size:1.1rem;line-height:1;">&times;</button>';
            container.appendChild(toast);
            setTimeout(() => {
                if (toast.parentNode) { toast.classList.add('removing'); setTimeout(() => toast.remove(), 300); }
            }, 5000);
        }
        @if(session('success'))
            showToast('{{ session('success') }}', 'success');
        @endif
        @if(session('error'))
            showToast('{{ session('error') }}', 'error');
        @endif
        @if(session('warning'))
            showToast('{{ session('warning') }}', 'warning');
        @endif
    </script>
    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('{{ asset("sw.js") }}').catch(() => {});
        }
    </script>
    <script>
        AOS.init({ duration: 600, once: true, offset: 30 });

        document.addEventListener('DOMContentLoaded', function() {
            const toggleBtn = document.getElementById('sidebarToggle');
            const sidebar = document.getElementById('sidebar');
            if (toggleBtn && sidebar) {
                toggleBtn.addEventListener('click', function() {
                    sidebar.classList.toggle('show');
                });
            }

            // Dark mode
            const dmBtn = document.getElementById('darkModeToggle');
            if (dmBtn) {
                const saved = localStorage.getItem('theme');
                if (saved === 'dark') {
                    document.documentElement.setAttribute('data-theme', 'dark');
                    dmBtn.innerHTML = '<i class="bi bi-sun"></i>';
                }
                dmBtn.addEventListener('click', function() {
                    const isDark = document.documentElement.getAttribute('data-theme') === 'dark';
                    if (isDark) {
                        document.documentElement.removeAttribute('data-theme');
                        localStorage.setItem('theme', 'light');
                        dmBtn.innerHTML = '<i class="bi bi-moon-stars"></i>';
                    } else {
                        document.documentElement.setAttribute('data-theme', 'dark');
                        localStorage.setItem('theme', 'dark');
                        dmBtn.innerHTML = '<i class="bi bi-sun"></i>';
                    }
                });
            }
        });
    </script>
    @stack('scripts')
    <div id="modal-root"></div>
</body>
</html>
