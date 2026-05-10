{{-- resources/views/auth/verify-email.blade.php --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Verify Email | Trip Planner Nepal</title>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <style>
        /* Same base styles – reuse from forgot-password */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #0a2b2e;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
            position: relative;
            overflow-x: hidden;
        }
        .sky-bg {
            position: fixed;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background: linear-gradient(145deg, #0f2c33 0%, #1e4b3e 100%);
            z-index: -2;
        }
        .cloud {
            position: absolute;
            background: rgba(255,255,255,0.2);
            border-radius: 1000px;
            filter: blur(40px);
            pointer-events: none;
            z-index: -1;
        }
        .cloud1 { width: 300px; height: 120px; top: 10%; left: -100px; animation: floatCloud 40s linear infinite; }
        .cloud2 { width: 450px; height: 180px; bottom: 15%; right: -150px; animation: floatCloud 50s linear infinite reverse; }
        .cloud3 { width: 200px; height: 80px; top: 40%; right: 10%; animation: floatCloud 30s linear infinite; }
        @keyframes floatCloud {
            0% { transform: translateX(0) translateY(0); }
            100% { transform: translateX(calc(100vw + 200px)) translateY(20px); }
        }
        .mountains {
            position: fixed;
            bottom: 0; left: 0;
            width: 100%; height: 40%;
            z-index: -1;
            opacity: 0.6;
        }
        .lang-switcher {
            position: fixed;
            top: 1.5rem; right: 1.5rem;
            z-index: 100;
            background: rgba(0,0,0,0.4);
            backdrop-filter: blur(8px);
            border-radius: 2rem;
            padding: 0.3rem;
            display: flex;
            gap: 0.3rem;
            border: 1px solid rgba(255,255,255,0.2);
        }
        .lang-btn {
            padding: 0.4rem 1rem;
            border-radius: 2rem;
            font-size: 0.8rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
            background: transparent;
            color: white;
            border: none;
        }
        .lang-btn.active { background: #d4af37; color: #1e4b3e; }
        .verify-wrapper {
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            display: flex;
            flex-wrap: wrap;
            background: rgba(255,255,255,0.95);
            backdrop-filter: blur(2px);
            border-radius: 2.5rem;
            box-shadow: 0 30px 50px rgba(0,0,0,0.2);
            overflow: hidden;
            animation: fadeUp 0.8s cubic-bezier(0.2,0.9,0.4,1.1);
        }
        @keyframes fadeUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .hero-panel {
            flex: 1;
            background: linear-gradient(135deg, #1e4b3e 0%, #0f2c33 100%);
            padding: 2.5rem 2rem;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            position: relative;
            overflow: hidden;
        }
        .hero-panel::before {
            content: "";
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 100%;
            background-image: url('data:image/svg+xml;utf8,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white" opacity="0.05"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>');
            background-repeat: repeat;
            background-size: 40px;
            pointer-events: none;
        }
        .brand {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 1.5rem;
            font-weight: 700;
        }
        .brand-icon { font-size: 2rem; }
        .tagline {
            margin-top: 2rem;
        }
        .tagline h1 {
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.2;
            margin-bottom: 1rem;
        }
        .tagline p {
            font-size: 1rem;
            opacity: 0.9;
            max-width: 85%;
        }
        .adventure-stats {
            display: flex;
            gap: 2rem;
            margin: 2rem 0;
        }
        .stat { text-align: center; }
        .stat-number { font-size: 1.8rem; font-weight: 700; }
        .stat-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; }
        .testimonial {
            border-top: 1px solid rgba(255,255,255,0.2);
            padding-top: 1.5rem;
            font-style: italic;
            font-size: 0.9rem;
        }
        .testimonial-author {
            margin-top: 0.5rem;
            font-weight: 500;
            font-size: 0.8rem;
        }
        .form-panel {
            flex: 1;
            padding: 2.5rem 2rem;
            background: white;
        }
        .form-header {
            margin-bottom: 2rem;
        }
        .form-header h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #1f3b3c;
        }
        .form-header p {
            color: #5b6e6e;
            margin-top: 0.3rem;
        }
        .status-message {
            background: #e6f9ed;
            border-left: 4px solid #2b8c5e;
            padding: 0.8rem 1rem;
            border-radius: 1rem;
            font-size: 0.85rem;
            color: #1e6f4c;
            margin-bottom: 1.5rem;
        }
        .btn-primary {
            background: #1f4e43;
            color: white;
            border: none;
            padding: 0.9rem 1.8rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.25s;
            font-family: 'Inter', sans-serif;
            letter-spacing: 0.5px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.05);
        }
        .btn-primary:hover {
            background: #0f3a30;
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(31,78,67,0.2);
        }
        .btn-secondary {
            background: transparent;
            color: #1f4e43;
            border: 1px solid #1f4e43;
            padding: 0.9rem 1.8rem;
            font-size: 0.9rem;
            font-weight: 600;
            border-radius: 2rem;
            cursor: pointer;
            transition: all 0.25s;
            font-family: 'Inter', sans-serif;
        }
        .btn-secondary:hover {
            background: #1f4e43;
            color: white;
        }
        @media (max-width: 850px) {
            .verify-wrapper { flex-direction: column; border-radius: 1.8rem; }
            .hero-panel { text-align: center; padding: 2rem; }
            .tagline p { max-width: 100%; }
            .adventure-stats { justify-content: center; }
            .form-panel { padding: 2rem; }
            .lang-switcher { top: 1rem; right: 1rem; }
            .flex.justify-between { flex-direction: column; gap: 1rem; align-items: center; }
        }
    </style>
</head>
<body x-data="{ lang: localStorage.getItem('lang') || 'en' }" x-init="$watch('lang', value => localStorage.setItem('lang', value)); document.documentElement.lang = lang;">
    <div class="sky-bg"></div>
    <div class="cloud cloud1"></div>
    <div class="cloud cloud2"></div>
    <div class="cloud cloud3"></div>
    <svg class="mountains" viewBox="0 0 1440 320" preserveAspectRatio="none">
        <path fill="rgba(255,255,255,0.1)" fill-opacity="0.5" d="M0,192L48,176C96,160,192,128,288,138.7C384,149,480,203,576,213.3C672,224,768,192,864,170.7C960,149,1056,139,1152,144C1248,149,1344,171,1392,181.3L1440,192L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
        <path fill="rgba(255,255,255,0.2)" fill-opacity="0.4" d="M0,224L48,213.3C96,203,192,181,288,181.3C384,181,480,203,576,208C672,213,768,203,864,197.3C960,192,1056,192,1152,197.3C1248,203,1344,213,1392,218.7L1440,224L1440,320L1392,320C1344,320,1248,320,1152,320C1056,320,960,320,864,320C768,320,672,320,576,320C480,320,384,320,288,320C192,320,96,320,48,320L0,320Z"></path>
    </svg>

    <div class="lang-switcher">
        <button class="lang-btn" :class="{ 'active': lang === 'en' }" @click="lang = 'en'">EN</button>
        <button class="lang-btn" :class="{ 'active': lang === 'ne' }" @click="lang = 'ne'">नेपाली</button>
    </div>

    <div class="verify-wrapper">
        <div class="hero-panel">
            <div>
                <div class="brand">
                    <span class="brand-icon">⛰️</span>
                    <span x-text="lang === 'en' ? 'Trip Planner Nepal' : 'यात्रा योजनाकार नेपाल'"></span>
                </div>
                <div class="tagline">
                    <h1 x-html="lang === 'en' ? 'Verify your<br>email address' : 'आफ्नो<br>इमेल ठेगाना प्रमाणित गर्नुहोस्'"></h1>
                    <p x-text="lang === 'en' ? 'One last step to unlock all the features of your travel planner.' : 'तपाईंको यात्रा योजनाकारका सबै सुविधाहरू अनलक गर्न अन्तिम चरण।'"></p>
                </div>
                <div class="adventure-stats">
                    <div class="stat"><div class="stat-number">100+</div><div class="stat-label" x-text="lang === 'en' ? 'Curated Trips' : 'चयन गरिएका यात्राहरू'"></div></div>
                    <div class="stat"><div class="stat-number">10k+</div><div class="stat-label" x-text="lang === 'en' ? 'Happy Travelers' : 'खुसी यात्रीहरू'"></div></div>
                    <div class="stat"><div class="stat-number">24/7</div><div class="stat-label" x-text="lang === 'en' ? 'Local Support' : 'स्थानीय सहयोग'"></div></div>
                </div>
            </div>
            <div class="testimonial">
                <span x-text="lang === 'en' ? '“Email verification was instant — I was planning my trek within minutes.”' : '“इमेल प्रमाणीकरण तुरुन्तै भयो — म केही मिनेटमा मेरो ट्रेक योजना गर्दै थिएँ।”'"></span>
                <div class="testimonial-author" x-text="lang === 'en' ? '— David, Canada' : '— डेभिड, क्यानडा'"></div>
            </div>
        </div>

        <div class="form-panel">
            <div class="form-header">
                <h2 x-text="lang === 'en' ? 'Verify your email' : 'आफ्नो इमेल प्रमाणित गर्नुहोस्'"></h2>
                <p x-text="lang === 'en' ? 'Thanks for signing up! Please verify your email address.' : 'साइन अप गर्नुभएकोमा धन्यवाद! कृपया आफ्नो इमेल ठेगाना प्रमाणित गर्नुहोस्।'"></p>
            </div>

            @if (session('status') == 'verification-link-sent')
                <div class="status-message">
                    {{ __('A new verification link has been sent to the email address you provided during registration.') }}
                </div>
            @endif

            <div class="space-y-4">
                <p class="text-sm text-gray-600" x-text="lang === 'en' ? 'Before proceeding, please check your email for a verification link.' : 'अगाडि बढ्नु अघि, कृपया प्रमाणीकरण लिंकको लागि आफ्नो इमेल जाँच गर्नुहोस्।'"></p>
                <p class="text-sm text-gray-600" x-text="lang === 'en' ? 'If you did not receive the email, we will gladly send you another.' : 'यदि तपाईंले इमेल प्राप्त गर्नुभएन भने, हामी तपाईंलाई अर्को पठाउनेछौं।'"></p>

                <div class="flex justify-between items-center mt-6">
                    <form method="POST" action="{{ route('verification.send') }}">
                        @csrf
                        <button type="submit" class="btn-primary" x-text="lang === 'en' ? 'Resend verification email' : 'प्रमाणीकरण इमेल पुन: पठाउनुहोस्'"></button>
                    </form>

                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="btn-secondary" x-text="lang === 'en' ? 'Log out' : 'लगआउट'"></button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
