{{-- resources/views/auth/login.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login | Life Replay — Rewind. Reflect. Relive.</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: linear-gradient(135deg, #e6f7ff 0%, #ffffff 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Soft animated background blobs */
        .blob {
            position: absolute;
            border-radius: 50%;
            filter: blur(60px);
            opacity: 0.3;
            pointer-events: none;
            z-index: 0;
            animation: floatBlob 20s infinite alternate ease-in-out;
        }

        @keyframes floatBlob {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(30px, -30px) scale(1.1); }
        }

        /* Floating light droplets */
        .droplet {
            position: absolute;
            background: radial-gradient(circle, rgba(56, 189, 248, 0.3), rgba(56, 189, 248, 0));
            border-radius: 50%;
            pointer-events: none;
            animation: floatDroplet 12s infinite ease-in-out;
            z-index: 0;
        }

        @keyframes floatDroplet {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            20% { opacity: 0.4; }
            100% { transform: translateY(-80px) rotate(20deg); opacity: 0; }
        }

        /* Soft floating memory tags */
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
            animation: flakeDrift 15s infinite alternate;
            z-index: 0;
            box-shadow: 0 2px 6px rgba(0,0,0,0.02);
        }

        @keyframes flakeDrift {
            0% { transform: translateX(0) translateY(0) rotate(0deg); opacity: 0.2; }
            50% { opacity: 0.7; }
            100% { transform: translateX(20px) translateY(-20px) rotate(3deg); opacity: 0.2; }
        }

        /* Main card – glassmorphism */
        .login-wrapper {
            max-width: 1100px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            background: rgba(255, 255, 255, 0.45);
            backdrop-filter: blur(16px);
            border-radius: 2.5rem;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.1), 0 0 0 1px rgba(14, 165, 233, 0.2);
            overflow: hidden;
            animation: cardFloat 0.9s cubic-bezier(0.16, 1, 0.3, 1);
            position: relative;
            z-index: 2;
        }

        @keyframes cardFloat {
            0% { opacity: 0; transform: translateY(40px) scale(0.98); }
            100% { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Left Panel – light blue gradient with soft waves */
        .hero-panel {
            flex: 1;
            background: linear-gradient(125deg, #7dd3fc, #bae6fd, #e0f2fe);
            padding: 2.5rem 2rem;
            color: #0c4a6e;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }

        .hero-panel::after {
            content: "";
            position: absolute;
            inset: 0;
            background: repeating-linear-gradient(45deg, rgba(255,255,255,0.1) 0px, rgba(255,255,255,0.1) 2px, transparent 2px, transparent 8px);
            pointer-events: none;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 0.6rem;
            font-size: 1.5rem;
            font-weight: 700;
            letter-spacing: -0.02em;
            position: relative;
            z-index: 1;
        }

        .brand-icon {
            font-size: 2rem;
            filter: drop-shadow(0 2px 4px rgba(0,0,0,0.05));
        }

        .tagline {
            margin-top: 2rem;
            position: relative;
            z-index: 1;
        }

        .tagline h1 {
            font-size: 2.4rem;
            font-weight: 800;
            line-height: 1.2;
            margin-bottom: 0.8rem;
            color: #0c4a6e;
        }

        .tagline p {
            font-size: 1rem;
            opacity: 0.85;
            max-width: 85%;
            color: #0f172a;
        }

        .stats {
            display: flex;
            gap: 2rem;
            margin: 2rem 0;
            position: relative;
            z-index: 1;
        }

        .stat {
            text-align: center;
        }

        .stat-number {
            font-size: 1.8rem;
            font-weight: 800;
            color: #0284c7;
        }

        .stat-label {
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
        }

        .testimonial {
            border-top: 1px solid rgba(12, 74, 110, 0.15);
            padding-top: 1.5rem;
            font-style: italic;
            font-size: 0.85rem;
            color: #1e3a5f;
            position: relative;
            z-index: 1;
        }

        .testimonial-author {
            margin-top: 0.5rem;
            font-weight: 600;
            font-size: 0.75rem;
        }

        /* Right Panel – clean white glass */
        .form-panel {
            flex: 1;
            padding: 2.5rem 2rem;
            background: rgba(255, 255, 255, 0.7);
            backdrop-filter: blur(4px);
        }

        .form-header {
            margin-bottom: 2rem;
        }

        .form-header h2 {
            font-size: 1.8rem;
            font-weight: 700;
            color: #0c4a6e;
            letter-spacing: -0.01em;
        }

        .form-header p {
            color: #475569;
            margin-top: 0.3rem;
        }

        .alert-success {
            background: #d1fae5;
            border-left: 4px solid #10b981;
            padding: 0.8rem 1rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            color: #065f46;
            margin-bottom: 1.5rem;
        }

        .input-group {
            margin-bottom: 1.5rem;
        }

        .input-group label {
            display: block;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            color: #0c4a6e;
            margin-bottom: 0.5rem;
        }

        .input-group input {
            width: 100%;
            padding: 0.85rem 1rem;
            font-size: 0.95rem;
            border: 1px solid #cbd5e1;
            border-radius: 1.2rem;
            background: white;
            color: #0f172a;
            transition: all 0.2s;
            font-family: 'Inter', sans-serif;
        }

        .input-group input:focus {
            outline: none;
            border-color: #38bdf8;
            box-shadow: 0 0 0 3px rgba(56, 189, 248, 0.3);
        }

        .error {
            font-size: 0.7rem;
            color: #ef4444;
            margin-top: 0.3rem;
            display: block;
        }

        .form-options {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin: 1rem 0 1.8rem;
        }

        .checkbox-label {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.85rem;
            color: #334155;
            cursor: pointer;
        }

        .checkbox-label input {
            width: 1rem;
            height: 1rem;
            accent-color: #38bdf8;
        }

        .forgot-link {
            font-size: 0.85rem;
            color: #0284c7;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s;
        }

        .forgot-link:hover {
            text-decoration: underline;
            color: #0c4a6e;
        }

        .btn-login {
            width: 100%;
            background: linear-gradient(95deg, #38bdf8, #0ea5e9);
            color: white;
            border: none;
            padding: 0.9rem;
            font-size: 0.95rem;
            font-weight: 600;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.25s;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 12px rgba(2, 132, 199, 0.2);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(2, 132, 199, 0.3);
        }

        .register-link {
            text-align: center;
            margin-top: 2rem;
            font-size: 0.85rem;
            color: #64748b;
            border-top: 1px solid #e2e8f0;
            padding-top: 1.5rem;
        }

        .register-link a {
            color: #0284c7;
            font-weight: 600;
            text-decoration: none;
            margin-left: 0.3rem;
            transition: color 0.2s;
        }

        .register-link a:hover {
            text-decoration: underline;
            color: #0c4a6e;
        }

        /* Responsive */
        @media (max-width: 850px) {
            .login-wrapper {
                flex-direction: column;
                border-radius: 1.8rem;
            }
            .hero-panel {
                text-align: center;
                padding: 2rem;
            }
            .tagline p {
                max-width: 100%;
            }
            .stats {
                justify-content: center;
            }
            .form-panel {
                padding: 2rem;
            }
        }
    </style>
</head>
<body>
    <!-- Background blobs -->
    <div class="blob" style="width: 300px; height: 300px; background: #7dd3fc; top: -100px; left: -100px;"></div>
    <div class="blob" style="width: 400px; height: 400px; background: #bae6fd; bottom: -150px; right: -100px; animation-duration: 25s;"></div>

    <!-- Droplets and memory flakes (JS generated) -->
    <div id="dropletsContainer" class="absolute inset-0 pointer-events-none z-0"></div>
    <div id="flakesContainer" class="absolute inset-0 pointer-events-none z-0"></div>

    <div class="login-wrapper">
        <!-- Left Panel -->
        <div class="hero-panel">
            <div>
                <div class="brand">
                    <span class="brand-icon">⌛</span>
                    <span>Life Replay</span>
                </div>
                <div class="tagline">
                    <h1>Rewind.<br>Reflect.<br>Relive.</h1>
                    <p>Capture every moment, replay your timeline, and rediscover your story.</p>
                </div>
                <div class="stats">
                    <div class="stat">
                        <div class="stat-number">∞</div>
                        <div class="stat-label">Moments</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">AI</div>
                        <div class="stat-label">Insights</div>
                    </div>
                    <div class="stat">
                        <div class="stat-number">Private</div>
                        <div class="stat-label">Vault</div>
                    </div>
                </div>
            </div>
            <div class="testimonial">
                “Life Replay turned my scattered memories into a beautiful timeline. I finally see my own growth.”
                <div class="testimonial-author">— Rahul, early user</div>
            </div>
        </div>

        <!-- Right Panel: Login Form -->
        <div class="form-panel">
            <div class="form-header">
                <h2>Welcome back</h2>
                <p>Sign in to continue your timeline</p>
            </div>

            @if (session('status'))
                <div class="alert-success">
                    {{ session('status') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login') }}">
                @csrf

                <div class="input-group">
                    <label for="email">Email address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="hello@life.com">
                    @error('email')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="input-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required autocomplete="current-password" placeholder="••••••••">
                    @error('password')
                        <span class="error">{{ $message }}</span>
                    @enderror
                </div>

                <div class="form-options">
                    <label class="checkbox-label">
                        <input type="checkbox" name="remember">
                        <span>Keep me signed in</span>
                    </label>
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="forgot-link">Forgot password?</a>
                    @endif
                </div>

                <button type="submit" class="btn-login">Unlock my timeline →</button>

                <div class="register-link">
                    <span>No account yet?</span>
                    <a href="{{ route('register') }}">Start your first replay →</a>
                </div>
            </form>
        </div>
    </div>

    <script>
        (function() {
            // Floating droplets
            const droplets = document.getElementById('dropletsContainer');
            for (let i = 0; i < 40; i++) {
                let d = document.createElement('div');
                d.classList.add('droplet');
                let size = Math.random() * 40 + 10;
                d.style.width = size + 'px';
                d.style.height = size + 'px';
                d.style.left = Math.random() * 100 + '%';
                d.style.top = Math.random() * 100 + '%';
                d.style.animationDuration = 8 + Math.random() * 12 + 's';
                d.style.animationDelay = Math.random() * 12 + 's';
                droplets.appendChild(d);
            }

            // Memory flakes (soft tags)
            const flakes = document.getElementById('flakesContainer');
            const memories = ["Trip to Pokhara 🚗", "Best exam result 🎓", "Night thoughts 🌙", "First love 💕", "Career milestone 🚀", "Sunrise meditation 🧘", "Friends reunion 🍕", "Late night coding 🌃", "Beach sunset 🌊", "Unexpected gift 🎁"];
            for (let i = 0; i < 18; i++) {
                let f = document.createElement('div');
                f.classList.add('memory-flake');
                f.innerText = memories[Math.floor(Math.random() * memories.length)];
                f.style.left = Math.random() * 85 + 5 + '%';
                f.style.top = Math.random() * 85 + 5 + '%';
                f.style.animationDuration = 12 + Math.random() * 12 + 's';
                f.style.animationDelay = Math.random() * 10 + 's';
                flakes.appendChild(f);
            }
        })();
    </script>
</body>
</html>
