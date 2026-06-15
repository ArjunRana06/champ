<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Sign In · AI Study Assistant</title>
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
        .input-group { margin-bottom: 1.4rem; }
        .input-group label {
            display: block; font-size: 0.7rem; font-weight: 600; text-transform: uppercase;
            letter-spacing: 0.05em; color: #6366f1; margin-bottom: 0.5rem;
        }
        .input-group input {
            width: 100%; padding: 0.85rem 1.1rem; font-size: 0.95rem;
            border: 1.5px solid #e5e7eb; border-radius: 1rem;
            background: white; color: #111827; transition: all 0.25s;
            font-family: 'Inter', sans-serif;
        }
        .input-group input:focus {
            outline: none; border-color: #6366f1; box-shadow: 0 0 0 4px rgba(99,102,241,0.1);
        }
        .input-group input::placeholder { color: #9ca3af; }
        .error { font-size: 0.7rem; color: #ef4444; margin-top: 0.3rem; display: block; }
        .form-options {
            display: flex; justify-content: space-between; align-items: center;
            margin-bottom: 1.5rem;
        }
        .checkbox-label { display: flex; align-items: center; gap: 0.5rem; font-size: 0.8rem; color: #4b5563; cursor: pointer; }
        .checkbox-label input { width: 1rem; height: 1rem; accent-color: #6366f1; }
        .forgot-link { font-size: 0.8rem; color: #6366f1; text-decoration: none; font-weight: 500; }
        .forgot-link:hover { text-decoration: underline; }
        .form-actions { display: flex; justify-content: space-between; align-items: center; gap: 1rem; margin-top: 0.5rem; }
        .register-link { font-size: 0.85rem; color: #6366f1; text-decoration: none; font-weight: 500; white-space: nowrap; }
        .register-link:hover { text-decoration: underline; }
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
        .login-btn {
            display: inline-flex; align-items: center; gap: 0.5rem;
            padding: 0.85rem 1.8rem; font-size: 0.9rem; font-weight: 600;
            border: none; border-radius: 9999px; cursor: pointer;
            font-family: 'Inter', sans-serif;
            background: #111827; color: white;
            transition: all 0.25s; box-shadow: 0 4px 14px rgba(0,0,0,0.1);
        }
        .login-btn:hover { background: #1f2937; transform: translateY(-1px); box-shadow: 0 8px 25px rgba(0,0,0,0.15); }
        @media (max-width: 820px) {
            .wrapper { flex-direction: column; border-radius: 1.8rem; }
            .hero-panel { text-align: center; padding: 2rem; }
            .tagline p { max-width: 100%; }
            .stats { justify-content: center; }
            .form-panel { padding: 2rem; }
            .form-options { flex-direction: column; gap: 0.8rem; align-items: flex-start; }
            .form-actions { flex-direction: column; align-items: stretch; }
            .login-btn { width: 100%; justify-content: center; }
            .register-link { text-align: center; }
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
                    <h1>Welcome back.<br>Ready to learn?</h1>
                    <p>Access your AI‑powered study hub — chat with your notes, generate quizzes, and master any subject.</p>
                </div>
                <div class="stats">
                    <div class="stat"><div class="stat-number">10k+</div><div class="stat-label">Students</div></div>
                    <div class="stat"><div class="stat-number">50k+</div><div class="stat-label">Notes processed</div></div>
                    <div class="stat"><div class="stat-number">98%</div><div class="stat-label">Success rate</div></div>
                </div>
            </div>
            <div class="testimonial">
                “The AI explains concepts exactly from my uploaded slides. It's like having a tutor that knows only my syllabus.”
                <div class="testimonial-author">— Riya, Engineering student</div>
            </div>
        </div>

        <div class="form-panel">
            <div class="form-header">
                <h2>Sign in</h2>
                <p>Enter your credentials to access your dashboard</p>
            </div>

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="input-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="student@example.com">
                    @error('email')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password')<span class="error">{{ $message }}</span>@enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                        <span>Remember me</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                <div class="form-actions">
                    <a href="{{ route('register') }}" class="register-link">Create new account →</a>
                    <button type="submit" class="login-btn">
                        Sign in <i class="fas fa-arrow-right text-sm"></i>
                    </button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>
