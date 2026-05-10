<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ config('app.name') }} - Dashboard</title>

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- AOS (Animate on Scroll) -->
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

    <style>
        /* ----- GLOBAL STYLES (Light Blue / Glassmorphism) ----- */
        :root {
            --primary-light: #7dd3fc;
            --primary: #0ea5e9;
            --primary-dark: #0284c7;
            --secondary: #bae6fd;
            --accent: #38bdf8;
            --glass-bg: rgba(255, 255, 255, 0.45);
            --glass-border: rgba(14, 165, 233, 0.2);
            --shadow-sm: 0 2px 8px rgba(0,0,0,0.02);
            --shadow-md: 0 4px 12px rgba(0,0,0,0.05);
            --shadow-lg: 0 12px 24px rgba(0,0,0,0.08);
            --card-radius: 1.5rem;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            transition: all 0.2s ease;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #e6f7ff 0%, #ffffff 100%);
            color: #0c4a6e;
            overflow-x: hidden;
            position: relative;
        }

        /* Floating background particles (droplets & memory tags) */
        .droplet {
            position: absolute;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.25), rgba(56, 189, 248, 0));
            border-radius: 50%;
            pointer-events: none;
            animation: floatDroplet 14s infinite ease-in-out;
            z-index: 0;
        }

        @keyframes floatDroplet {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            20% { opacity: 0.5; }
            100% { transform: translateY(-100px) rotate(15deg); opacity: 0; }
        }

        .memory-flake {
            position: absolute;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(8px);
            border-radius: 2rem;
            padding: 0.3rem 0.9rem;
            font-size: 0.7rem;
            font-weight: 500;
            color: #0284c7;
            border: 1px solid rgba(14, 165, 233, 0.3);
            white-space: nowrap;
            pointer-events: none;
            animation: flakeDrift 16s infinite alternate;
            z-index: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        @keyframes flakeDrift {
            0% { transform: translateX(0) translateY(0) rotate(0deg); opacity: 0.2; }
            50% { opacity: 0.6; }
            100% { transform: translateX(25px) translateY(-25px) rotate(3deg); opacity: 0.2; }
        }

        /* Dashboard wrapper */
        .dashboard-wrapper {
            display: flex;
            min-height: 100vh;
            position: relative;
            z-index: 1;
        }

        /* ----- SIDEBAR (Glassmorphism Light) ----- */
        .sidebar {
            width: 85px;
            background: rgba(255, 255, 255, 0.35);
            backdrop-filter: blur(16px);
            border-right: 1px solid var(--glass-border);
            transition: width 0.3s cubic-bezier(0.2, 0.9, 0.4, 1.1);
            position: relative;
            z-index: 10;
            overflow: hidden;
            box-shadow: var(--shadow-md);
        }

        .sidebar:hover {
            width: 280px;
        }

        .sidebar .sidebar-header {
            padding: 25px 0;
            text-align: center;
            border-bottom: 1px solid rgba(14, 165, 233, 0.2);
            margin-bottom: 20px;
            white-space: nowrap;
        }

        .sidebar .sidebar-header h4 {
            font-weight: 700;
            font-size: 1.5rem;
            background: linear-gradient(135deg, #0284c7, #38bdf8);
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            opacity: 0;
            transition: opacity 0.2s 0.1s;
        }

        .sidebar:hover .sidebar-header h4 {
            opacity: 1;
        }

        .sidebar .nav {
            flex-direction: column;
            padding: 0 12px;
        }

        .sidebar .nav-item {
            width: 100%;
            margin-bottom: 6px;
            position: relative;
        }

        .sidebar .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 14px;
            color: #0c4a6e;
            border-radius: 12px;
            white-space: nowrap;
            gap: 14px;
            font-weight: 500;
            transition: all 0.25s;
        }

        .sidebar .nav-link i {
            font-size: 1.4rem;
            min-width: 28px;
            text-align: center;
        }

        .sidebar .nav-link span {
            opacity: 0;
            transition: opacity 0.2s;
            font-size: 0.9rem;
        }

        .sidebar:hover .nav-link span {
            opacity: 1;
        }

        .sidebar .nav-link:hover,
        .sidebar .nav-link.active {
            background: rgba(56, 189, 248, 0.3);
            color: #0284c7;
            transform: translateX(4px);
        }

        .sidebar .nav-link .badge {
            margin-left: auto;
            background: #f72585;
            color: white;
            font-size: 0.65rem;
            padding: 2px 6px;
            border-radius: 20px;
            opacity: 0;
        }

        .sidebar:hover .nav-link .badge {
            opacity: 1;
        }

        /* Submenu inside sidebar */
        .sidebar .collapse .nav-link {
            padding-left: 45px;
            font-size: 0.85rem;
        }

        /* ----- MAIN CONTENT ----- */
        .main-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: transparent;
        }

        /* Top Navbar (Glass) */
        .navbar-top {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(12px);
            padding: 10px 24px;
            border-bottom: 1px solid var(--glass-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 5;
        }

        .search-box {
            position: relative;
            width: 320px;
        }

        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #7dd3fc;
        }

        .search-box input {
            width: 100%;
            padding: 10px 18px 10px 42px;
            border: 1px solid var(--glass-border);
            border-radius: 40px;
            background: rgba(255,255,255,0.7);
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .search-box input:focus {
            outline: none;
            background: white;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(14, 165, 233, 0.2);
        }

        .user-menu {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .notification-icon {
            position: relative;
            width: 40px;
            height: 40px;
            background: rgba(255,255,255,0.7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
        }

        .notification-icon:hover {
            background: white;
            transform: scale(1.05);
        }

        .notification-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: linear-gradient(135deg, #f72585, #b5179e);
            color: white;
            border-radius: 50%;
            width: 18px;
            height: 18px;
            font-size: 0.65rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid white;
        }

        .user-dropdown {
            display: flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            padding: 4px 10px 4px 4px;
            border-radius: 40px;
            background: rgba(255,255,255,0.7);
        }

        .user-dropdown:hover {
            background: white;
        }

        .user-avatar {
            width: 40px;
            height: 40px;
            background: linear-gradient(135deg, #0ea5e9, #0284c7);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .user-info .name {
            font-weight: 600;
            font-size: 0.9rem;
            color: #0c4a6e;
        }

        .user-info .email {
            font-size: 0.7rem;
            color: #475569;
        }

        /* Footer */
        .dashboard-footer {
            background: rgba(255, 255, 255, 0.5);
            backdrop-filter: blur(8px);
            border-top: 1px solid var(--glass-border);
            padding: 15px 24px;
            font-size: 0.85rem;
            color: #475569;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: auto;
        }

        .dashboard-footer a {
            color: #475569;
            text-decoration: none;
            margin-left: 20px;
        }

        .dashboard-footer a:hover {
            color: var(--primary);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 0;
                position: fixed;
                height: 100vh;
                left: 0;
                top: 0;
                z-index: 1050;
            }
            .sidebar.show {
                width: 280px;
            }
            .search-box {
                width: 180px;
            }
            .user-info {
                display: none;
            }
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 6px;
        }
        ::-webkit-scrollbar-track {
            background: #e0f2fe;
            border-radius: 10px;
        }
        ::-webkit-scrollbar-thumb {
            background: #7dd3fc;
            border-radius: 10px;
        }
    </style>
</head>
<body>
    <!-- Background floating elements (same as login page) -->
    <div id="floatingBackground"></div>

    <div class="dashboard-wrapper">
        @include('Backend.sidebar')
        <div class="main-content">
            @include('Backend.navbar')
            <main style="padding: 24px;">
                @yield('content')
            </main>
            @include('Backend.footer')
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // Generate floating droplets and memory tags (same as login)
        (function() {
            const container = document.getElementById('floatingBackground');
            if (!container) return;
            for (let i = 0; i < 35; i++) {
                let d = document.createElement('div');
                d.classList.add('droplet');
                let size = Math.random() * 40 + 10;
                d.style.width = size + 'px';
                d.style.height = size + 'px';
                d.style.left = Math.random() * 100 + '%';
                d.style.top = Math.random() * 100 + '%';
                d.style.animationDuration = 10 + Math.random() * 15 + 's';
                d.style.animationDelay = Math.random() * 12 + 's';
                container.appendChild(d);
            }
            const memories = ["Life Replay"];
            for (let i = 0; i < 2; i++) {
                let f = document.createElement('div');
                f.classList.add('memory-flake');
                f.innerText = memories[Math.floor(Math.random() * memories.length)];
                f.style.left = Math.random() * 85 + 5 + '%';
                f.style.top = Math.random() * 85 + 5 + '%';
                f.style.animationDuration = 12 + Math.random() * 12 + 's';
                f.style.animationDelay = Math.random() * 10 + 's';
                container.appendChild(f);
            }
        })();

        // AOS
        AOS.init({ duration: 600, once: true, offset: 30 });

        // Sidebar toggle for mobile
        const toggleBtn = document.getElementById('sidebarToggle');
        const sidebar = document.getElementById('sidebar');
        if (toggleBtn && sidebar) {
            toggleBtn.addEventListener('click', () => sidebar.classList.toggle('show'));
        }
    </script>
</body>
</html>
