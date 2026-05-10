<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>{{ config('app.name') }} - Dashboard</title>

<!-- Google Fonts (Inter + Poppins) -->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">

<!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

<!-- Bootstrap CSS -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

<!-- AOS (Animate on Scroll) -->
<link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">

<!-- Custom Styles -->
<style>
    :root {
        --primary: #4361ee;
        --secondary: #3f37c9;
        --success: #4cc9f0;
        --danger: #f72585;
        --warning: #f8961e;
        --info: #4895ef;
        --dark: #1b1b2f;
        --light: #f8f9fa;
        --gradient-1: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --gradient-2: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
        --gradient-3: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
        --gradient-4: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);
        --gradient-5: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
        --glass-bg: rgba(255, 255, 255, 0.25);
        --glass-border: rgba(255, 255, 255, 0.18);
        --shadow-sm: 0 2px 4px rgba(0,0,0,0.02);
        --shadow-md: 0 4px 6px rgba(0,0,0,0.05);
        --shadow-lg: 0 10px 15px rgba(0,0,0,0.1);
        --shadow-xl: 0 20px 25px rgba(0,0,0,0.15);
    }

    body {
        font-family: 'Inter', sans-serif;
        background: #f5f7fa;
        color: #2d3748;
        overflow-x: hidden;
    }

    /* Smooth transitions */
    * {
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
    }

    /* Custom scrollbar */
    ::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    ::-webkit-scrollbar-track {
        background: #e9ecef;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb {
        background: #adb5bd;
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: #6c757d;
    }

    /* Dashboard layout */
    .dashboard-wrapper {
        display: flex;
        min-height: 100vh;
    }

    /* Sidebar - glassmorphic style */
    .sidebar {
        width: 85px;
        background: linear-gradient(180deg, #1e1e2f 0%, #2d2d44 100%);
        color: white;
        transition: width 0.3s ease;
        position: relative;
        box-shadow: 4px 0 20px rgba(0,0,0,0.2);
        overflow: hidden;
        z-index: 1000;
        backdrop-filter: blur(10px);
    }

    .sidebar:hover {
        width: 280px;
    }

    .sidebar .sidebar-header {
        padding: 25px 0;
        text-align: center;
        border-bottom: 1px solid rgba(255,255,255,0.1);
        margin-bottom: 20px;
        white-space: nowrap;
    }

    .sidebar .sidebar-header h4 {
        color: white;
        font-weight: 600;
        font-size: 1.5rem;
        opacity: 0;
        transition: opacity 0.2s 0.1s;
        background: linear-gradient(135deg, #a8edea, #fed6e3);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .sidebar:hover .sidebar-header h4 {
        opacity: 1;
    }

    .sidebar .nav {
        flex-direction: column;
        padding: 0 10px;
    }

    .sidebar .nav-item {
        width: 100%;
        margin-bottom: 8px;
        position: relative;
    }

    .sidebar .nav-link {
        display: flex;
        align-items: center;
        padding: 12px 15px;
        color: rgba(255,255,255,0.7);
        border-radius: 12px;
        white-space: nowrap;
        gap: 15px;
        font-weight: 500;
        transition: all 0.3s;
    }

    .sidebar .nav-link i {
        font-size: 1.6rem;
        min-width: 30px;
        text-align: center;
        filter: drop-shadow(0 2px 4px rgba(0,0,0,0.2));
    }

    .sidebar .nav-link span {
        opacity: 0;
        transition: opacity 0.2s;
        font-size: 1rem;
    }

    .sidebar:hover .nav-link span {
        opacity: 1;
    }

    .sidebar .nav-link:hover,
    .sidebar .nav-link.active {
        background: rgba(255,255,255,0.15);
        color: white;
        transform: translateX(8px);
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
    }

    .sidebar .nav-link .badge {
        margin-left: auto;
        background: var(--danger);
        color: white;
        font-size: 0.7rem;
        padding: 3px 6px;
        border-radius: 20px;
        opacity: 0;
        transition: opacity 0.2s;
    }

    .sidebar:hover .nav-link .badge {
        opacity: 1;
    }

    /* Main content area */
    .main-content {
        flex: 1;
        display: flex;
        flex-direction: column;
        background: #f8fafc;
    }

    /* Top navbar - glass effect */
    .navbar-top {
        background: rgba(255, 255, 255, 0.8);
        backdrop-filter: blur(10px);
        padding: 12px 24px;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        justify-content: space-between;
        position: sticky;
        top: 0;
        z-index: 999;
        border-bottom: 1px solid rgba(0,0,0,0.05);
    }

    .search-box {
        position: relative;
        width: 350px;
    }

    .search-box i {
        position: absolute;
        left: 15px;
        top: 50%;
        transform: translateY(-50%);
        color: #94a3b8;
        font-size: 1.1rem;
    }

    .search-box input {
        width: 100%;
        padding: 12px 20px 12px 45px;
        border: none;
        border-radius: 40px;
        background: #f1f5f9;
        font-size: 0.95rem;
        transition: all 0.3s;
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    }

    .search-box input:focus {
        outline: none;
        background: white;
        box-shadow: 0 0 0 3px rgba(67, 97, 238, 0.2);
    }

    .user-menu {
        display: flex;
        align-items: center;
        gap: 25px;
    }

    .notification-icon {
        position: relative;
        cursor: pointer;
        width: 45px;
        height: 45px;
        background: #f1f5f9;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s;
    }

    .notification-icon:hover {
        background: #e2e8f0;
        transform: scale(1.05);
    }

    .notification-icon i {
        font-size: 1.4rem;
        color: #334155;
    }

    .notification-badge {
        position: absolute;
        top: -2px;
        right: -2px;
        background: linear-gradient(135deg, #f72585, #b5179e);
        color: white;
        border-radius: 50%;
        width: 20px;
        height: 20px;
        font-size: 0.7rem;
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
        padding: 5px 10px 5px 5px;
        border-radius: 40px;
        background: #f1f5f9;
        transition: all 0.2s;
    }

    .user-dropdown:hover {
        background: #e2e8f0;
    }

    .user-avatar {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #4361ee, #3a0ca3);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 600;
        font-size: 1.2rem;
        box-shadow: 0 4px 6px rgba(67,97,238,0.3);
    }

    .user-info {
        display: none;
    }

    @media (min-width: 768px) {
        .user-info {
            display: block;
            line-height: 1.3;
        }
        .user-info .name {
            font-weight: 600;
            font-size: 0.95rem;
            color: #1e293b;
        }
        .user-info .email {
            font-size: 0.75rem;
            color: #64748b;
        }
    }

    /* Cards */
    .card-modern {
        background: white;
        border: none;
        border-radius: 24px;
        padding: 20px;
        box-shadow: var(--shadow-lg);
        transition: transform 0.3s, box-shadow 0.3s;
        height: 100%;
        position: relative;
        overflow: hidden;
    }

    .card-modern::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(67,97,238,0.1) 0%, transparent 70%);
        opacity: 0;
        transition: opacity 0.5s;
        pointer-events: none;
    }

    .card-modern:hover {
        transform: translateY(-8px);
        box-shadow: var(--shadow-xl);
    }

    .card-modern:hover::before {
        opacity: 1;
    }

    .stat-icon {
        width: 60px;
        height: 60px;
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 2.2rem;
        margin-bottom: 15px;
        box-shadow: 0 10px 15px -3px rgba(0,0,0,0.1);
    }

    .stat-icon.blue {
        background: linear-gradient(135deg, #4361ee, #3a0ca3);
        color: white;
    }

    .stat-icon.green {
        background: linear-gradient(135deg, #4cc9f0, #4895ef);
        color: white;
    }

    .stat-icon.orange {
        background: linear-gradient(135deg, #f8961e, #f3722c);
        color: white;
    }

    .stat-icon.pink {
        background: linear-gradient(135deg, #f72585, #b5179e);
        color: white;
    }

    /* Chart containers */
    .chart-card {
        background: white;
        border-radius: 24px;
        padding: 20px;
        box-shadow: var(--shadow-lg);
        margin-bottom: 24px;
    }

    .chart-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 20px;
    }

    .chart-header h5 {
        font-weight: 600;
        color: #1e293b;
    }

    /* Table */
    .table-modern {
        background: white;
        border-radius: 24px;
        overflow: hidden;
        box-shadow: var(--shadow-lg);
    }

    .table-modern thead th {
        background: #f8fafc;
        color: #475569;
        font-weight: 600;
        font-size: 0.85rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 18px 20px;
        border-bottom: 1px solid #e2e8f0;
    }

    .table-modern tbody td {
        padding: 16px 20px;
        border-bottom: 1px solid #e2e8f0;
        color: #334155;
        font-size: 0.95rem;
        vertical-align: middle;
    }

    .table-modern tbody tr:hover {
        background: #f1f5f9;
    }

    .badge-modern {
        padding: 6px 12px;
        border-radius: 40px;
        font-weight: 500;
        font-size: 0.75rem;
    }

    .badge-success {
        background: #d1fae5;
        color: #065f46;
    }

    .badge-warning {
        background: #fef3c7;
        color: #92400e;
    }

    .badge-info {
        background: #dbeafe;
        color: #1e40af;
    }

    .badge-danger {
        background: #fee2e2;
        color: #b91c1c;
    }

    /* Progress bar */
    .progress-modern {
        height: 8px;
        border-radius: 20px;
        background: #e2e8f0;
        overflow: hidden;
        width: 100px;
    }

    .progress-modern .progress-bar {
        background: linear-gradient(90deg, #4361ee, #3a0ca3);
        border-radius: 20px;
    }

    /* Footer */
    .dashboard-footer {
        background: white;
        padding: 20px 24px;
        border-top: 1px solid #e2e8f0;
        font-size: 0.9rem;
        color: #64748b;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: auto;
    }

    .dashboard-footer a {
        color: #64748b;
        text-decoration: none;
        margin-left: 20px;
        transition: color 0.2s;
    }

    .dashboard-footer a:hover {
        color: #4361ee;
    }

    /* Quick actions */
    .quick-actions {
        display: flex;
        gap: 12px;
        margin-bottom: 24px;
    }

    .quick-action-btn {
        background: white;
        border: none;
        border-radius: 18px;
        padding: 12px 20px;
        box-shadow: var(--shadow-md);
        display: flex;
        align-items: center;
        gap: 10px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s;
    }

    .quick-action-btn:hover {
        transform: translateY(-3px);
        box-shadow: var(--shadow-lg);
        background: #4361ee;
        color: white;
    }

    .quick-action-btn i {
        font-size: 1.3rem;
    }

    /* Responsive */
    @media (max-width: 768px) {
        .sidebar {
            width: 0;
            position: fixed;
            height: 100vh;
        }
        .sidebar.show {
            width: 280px;
        }
        .main-content {
            margin-left: 0;
        }
        .search-box {
            width: 200px;
        }
        .quick-actions {
            flex-wrap: wrap;
        }
    }

    /* Animations */
    [data-aos] {
        pointer-events: none;
    }
    [data-aos].aos-animate {
        pointer-events: auto;
    }
</style>
