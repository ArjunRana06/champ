<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Verify Email · Study Assistant for Students</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #f8fafc;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }
        .mesh-bg {
            position: fixed; inset: 0; z-index: -1;
            background:
                radial-gradient(ellipse 80% 60% at 0% 20%, rgba(99,102,241,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 100% 0%, rgba(139,92,246,0.1) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 30% 60%, rgba(6,182,212,0.06) 0%, transparent 50%);
            animation: meshShift 20s ease-in-out infinite alternate;
        }
        @keyframes meshShift {
            0% { transform: scale(1); opacity: 0.7; }
            100% { transform: scale(1.05); opacity: 1; }
        }
        .orb { position: fixed; border-radius: 50%; filter: blur(100px); pointer-events: none; z-index: -1; animation: orbFloat 25s ease-in-out infinite alternate; }
        .orb:nth-child(1) { width: 500px; height: 500px; background: rgba(99,102,241,0.15); top: -10%; left: -10%; }
        .orb:nth-child(2) { width: 400px; height: 400px; background: rgba(139,92,246,0.1); bottom: -5%; right: -5%; animation-delay: -8s; }
        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, -40px) scale(1.1); }
        }
        .wrapper {
            max-width: 1000px; width: 100%; margin: 0 auto;
            display: flex; flex-wrap: wrap;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(24px) saturate(1.8);
            border-radius: 2.5rem;
            box-shadow: 0 25px 60px -12px rgba(0,0,0,0.08), 0 0 0 1px rgba(255,255,255,0.5);
            overflow: hidden;
            animation: cardIn 0.8s cubic-bezier(0.16,1,0.3,1);
            position: relative; z-index: 2;
        }
        @keyframes cardIn {
            0% { opacity: 0; transform: translateY(30px) scale(0.97); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }
        .hero-panel {
            flex: 1; padding: 3rem 2.5rem;
            background: linear-gradient(135deg, #1e1b4b, #312e81, #581c87);
            color: white; display: flex; flex-direction: column; justify-content: space-between;
            position: relative; overflow: hidden;
        }
        .hero-panel::before {
            content: ''; position: absolute; inset: 0;
            background: repeating-linear-gradient(45deg, rgba(255,255,255,0.03) 0px, rgba(255,255,255,0.03) 2px, transparent 2px, transparent 8px);
            pointer-events: none;
        }
        .hero-panel::after {
            content: ''; position: absolute; inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% 0%, rgba(255,255,255,0.06) 0%, transparent 60%);
        }
        .brand { display: flex; align-items: center; gap: 0.7rem; font-size: 1.4rem; font-weight: 700; position: relative; z-index: 1; }
        .brand-icon { font-size: 1.8rem; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.2)); }
        .tagline { margin-top: 2.5rem; position: relative; z-index: 1; }
        .tagline h1 { font-size: 2.2rem; font-weight: 800; line-height: 1.2; }
        .tagline p { font-size: 0.95rem; opacity: 0.8; margin-top: 0.8rem; max-width: 88%; line-height: 1.6; }
        .stats { display: flex; gap: 2rem; margin: 2.5rem 0; position: relative; z-index: 1; }
        .stat { text-align: center; }
        .stat-number { font-size: 1.6rem; font-weight: 800; color: #c4b5fd; }
        .stat-label { font-size: 0.65rem; text-transform: uppercase; letter-spacing: 1px; font-weight: 600; opacity: 0.7; margin-top: 0.2rem; }
        .testimonial {
            border-top: 1px solid rgba(255,255,255,0.1); padding-top: 1.5rem;
            font-style: italic; font-size: 0.82rem; opacity: 0.85; position: relative; z-index: 1; line-height: 1.5;
        }
        .testimonial-author { margin-top: 0.5rem; font-weight: 600; font-size: 0.72rem; opacity: 0.7; font-style: normal; }
        .form-panel {
            flex: 1; padding: 3rem 2.5rem;
            background: rgba(255,255,255,0.5);
            backdrop-filter: blur(4px);
        }
        .form-header { margin-bottom: 2rem; }
        .form-header h2 { font-size: 1.6rem; font-weight: 800; color: #1e1b4b; }
        .form-header p { color: #6b7280; font-size: 0.9rem; margin-top: 0.3rem; }
        .status-message {
            background: #ecfdf5; border-left: 4px solid #10b981;
            padding: 0.8rem 1rem; border-radius: 0.8rem;
            font-size: 0.85rem; color: #065f46; margin-bottom: 1.5rem;
        }
        .info-text { font-size: 0.9rem; color: #4b5563; line-height: 1.6; margin-bottom: 0.5rem; }
        .animated-border {
            position: relative; border-radius: 9999px; overflow: hidden; display: inline-flex;
        }
        .animated-border::before {
            content: ''; position: absolute; inset: 0; border-radius: 9999px; padding: 2px;
            background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899, #6366f1);
            background-size: 300% 100%; animation: borderSpin 4s linear infinite;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor; mask-composite: exclude;
        }
        @keyframes borderSpin {
            0% { background-position: 0% 50%; }
            100% { background-position: 300% 50%; }
        }
        .btn-primary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.85rem 1.5rem; font-size: 0.85rem; font-weight: 600;
            border: none; border-radius: 9999px; cursor: pointer;
            font-family: 'Inter', sans-serif;
            background: #111827; color: white;
            transition: all 0.25s; box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }
        .btn-primary:hover { background: #1f2937; transform: translateY(-1px); }
        .btn-secondary {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.85rem 1.5rem; font-size: 0.85rem; font-weight: 600;
            border: 1.5px solid #d1d5db; border-radius: 9999px; cursor: pointer;
            font-family: 'Inter', sans-serif;
            background: transparent; color: #4b5563;
            transition: all 0.25s;
        }
        .btn-secondary:hover { background: #f9fafb; border-color: #9ca3af; }
        .action-group { display: flex; flex-wrap: wrap; gap: 1rem; margin-top: 1.5rem; align-items: center; }
        @media (max-width: 820px) {
            .wrapper { flex-direction: column; border-radius: 1.8rem; }
            .hero-panel { text-align: center; padding: 2rem; }
            .tagline p { max-width: 100%; }
            .stats { justify-content: center; }
            .form-panel { padding: 2rem; }
            .action-group { flex-direction: column; }
            .btn-primary, .btn-secondary { width: 100%; justify-content: center; }
        }
    </style>
</head>
<body>
    <div class="orb"></div><div class="orb"></div>
    <div class="mesh-bg"></div>

    <div class="wrapper">
        <div class="hero-panel">
            <div>
                <div class="brand">
                    <span class="brand-icon"><i class="fas fa-brain"></i></span>
                    <span>AI Study</span>
                </div>
                <div class="tagline">
                    <h1>Verify your<br>email address</h1>
                    <p>One last step to unlock all your AI-powered study features.</p>
                </div>
                <div class="stats">
                    <div class="stat"><div class="stat-number">10k+</div><div class="stat-label">Students</div></div>
                    <div class="stat"><div class="stat-number">50k+</div><div class="stat-label">Notes processed</div></div>
                    <div class="stat"><div class="stat-number">98%</div><div class="stat-label">Success rate</div></div>
                </div>
            </div>
            <div class="testimonial">
                “Email verification was instant — I was studying with AI within minutes.”
                <div class="testimonial-author">— David, Engineering student</div>
            </div>
        </div>

        <div class="form-panel">
            <div class="form-header">
                <h2>Verify your email</h2>
                <p>Thanks for signing up! Please verify your email address.</p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="status-message">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <p class="info-text">Before proceeding, please check your email for a verification link.</p>
            <p class="info-text">If you did not receive the email, we will gladly send you another.</p>

            <div class="action-group">
                <form method="POST" action="{{ route('verification.send') }}">
                    @csrf
                    <button type="submit" class="btn-primary">
                        <i class="fas fa-envelope"></i> Resend verification email
                    </button>
                </form>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="btn-secondary">Log out</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>
