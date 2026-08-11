<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Study Assistant for Students · Next‑Gen Learning</title>
    <meta name="description" content="Upload your notes and study smarter with AI. Chat with your materials, generate quizzes, flashcards, study plans, track time with Pomodoro, and master subjects faster.">
    <meta property="og:title" content="Study Assistant for Students · Next‑Gen Learning">
    <meta property="og:description" content="Upload your notes and study smarter with AI. Chat, generate quizzes, flashcards, study plans, Pomodoro timer, and more.">
    <meta property="og:type" content="website">
    <meta name="twitter:card" content="summary_large_image">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Space+Grotesk:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { scroll-behavior: smooth; }
        body { font-family: 'Inter', sans-serif; }

        /* Animated gradient mesh */
        .mesh-bg {
            position: fixed;
            inset: 0;
            z-index: -1;
            background:
                radial-gradient(ellipse 80% 60% at 0% 20%, rgba(99,102,241,0.15) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 100% 0%, rgba(139,92,246,0.12) 0%, transparent 60%),
                radial-gradient(ellipse 70% 40% at 50% 100%, rgba(236,72,153,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 30% 60%, rgba(6,182,212,0.06) 0%, transparent 50%);
            animation: meshShift 20s ease-in-out infinite alternate;
        }
        @keyframes meshShift {
            0% { transform: scale(1) rotate(0deg); opacity: 0.7; }
            50% { transform: scale(1.05) rotate(1deg); opacity: 1; }
            100% { transform: scale(1) rotate(-1deg); opacity: 0.8; }
        }

        /* Floating orbs */
        .orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(100px);
            pointer-events: none;
            z-index: -1;
            animation: orbFloat 25s ease-in-out infinite alternate;
        }
        .orb:nth-child(1) { width: 500px; height: 500px; background: rgba(99,102,241,0.2); top: -10%; left: -10%; }
        .orb:nth-child(2) { width: 400px; height: 400px; background: rgba(139,92,246,0.15); bottom: -5%; right: -5%; animation-delay: -8s; }
        .orb:nth-child(3) { width: 300px; height: 300px; background: rgba(236,72,153,0.1); top: 40%; left: 50%; animation-delay: -15s; }
        @keyframes orbFloat {
            0% { transform: translate(0, 0) scale(1); }
            100% { transform: translate(60px, -40px) scale(1.1); }
        }

        /* Glass card refined */
        .glass-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(20px) saturate(1.8);
            border: 1px solid rgba(255,255,255,0.5);
            border-radius: 2rem;
            box-shadow: 0 8px 32px rgba(0,0,0,0.04);
        }
        .glass-card-strong {
            background: rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(24px) saturate(2);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 2rem;
            box-shadow: 0 20px 60px rgba(0,0,0,0.06);
        }

        /* Animated border */
        .animated-border {
            position: relative;
            border-radius: 9999px;
            overflow: hidden;
        }
        .animated-border::before {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 9999px;
            padding: 2px;
            background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899, #6366f1);
            background-size: 300% 100%;
            animation: borderSpin 4s linear infinite;
            -webkit-mask: linear-gradient(#fff 0 0) content-box, linear-gradient(#fff 0 0);
            -webkit-mask-composite: xor;
            mask-composite: exclude;
        }
        @keyframes borderSpin {
            0% { background-position: 0% 50%; }
            100% { background-position: 300% 50%; }
        }

        /* Gradient text with glow */
        .text-glow {
            background: linear-gradient(135deg, #4f46e5, #a855f7, #ec4899);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: shimmer 4s linear infinite;
            filter: drop-shadow(0 0 30px rgba(168,85,247,0.15));
        }
        @keyframes shimmer {
            0% { background-position: 0% 50%; }
            100% { background-position: 200% 50%; }
        }

        /* Hero image mockup */
        .mockup {
            position: relative;
            border-radius: 1.5rem;
            overflow: hidden;
            box-shadow: 0 30px 80px rgba(79,70,229,0.15), 0 0 0 1px rgba(255,255,255,0.3);
            transform: perspective(1000px) rotateY(-2deg) rotateX(2deg);
            transition: transform 0.6s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .mockup:hover {
            transform: perspective(1000px) rotateY(0deg) rotateX(0deg);
        }
        .mockup-glow {
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(99,102,241,0.1), transparent, rgba(236,72,153,0.1));
            pointer-events: none;
            z-index: 1;
        }

        /* Feature cards with depth */
        .feature-card {
            position: relative;
            border-radius: 1.5rem;
            padding: 2rem;
            background: rgba(255,255,255,0.55);
            backdrop-filter: blur(16px) saturate(1.6);
            border: 1px solid rgba(255,255,255,0.4);
            box-shadow: 0 4px 20px rgba(0,0,0,0.03);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .feature-card:hover {
            transform: translateY(-6px) scale(1.01);
            box-shadow: 0 20px 60px rgba(99,102,241,0.1);
            border-color: rgba(99,102,241,0.2);
            background: rgba(255,255,255,0.7);
        }
        .feature-icon {
            width: 56px; height: 56px;
            border-radius: 1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            color: white;
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            position: relative;
        }
        .feature-icon::after {
            content: '';
            position: absolute;
            inset: -4px;
            border-radius: inherit;
            background: inherit;
            opacity: 0.2;
            filter: blur(8px);
            transition: all 0.4s;
        }
        .feature-card:hover .feature-icon {
            transform: scale(1.1) rotate(-3deg);
        }
        .feature-card:hover .feature-icon::after {
            opacity: 0.4;
            filter: blur(12px);
        }

        /* Step cards */
        .step-card {
            position: relative;
            border-radius: 1.5rem;
            padding: 2rem 1.5rem;
            background: rgba(255,255,255,0.4);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255,255,255,0.3);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            overflow: hidden;
        }
        .step-card::before {
            content: '';
            position: absolute;
            top: 0; left: 0;
            width: 100%; height: 4px;
            background: linear-gradient(90deg, #6366f1, #a855f7, #ec4899);
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .step-card:hover::before { transform: scaleX(1); }
        .step-card:hover {
            transform: translateY(-4px);
            background: rgba(255,255,255,0.6);
            box-shadow: 0 20px 50px rgba(99,102,241,0.08);
        }
        .step-number {
            width: 40px; height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 1.1rem;
            font-family: 'Space Grotesk', sans-serif;
            transition: all 0.4s;
        }
        .step-card:hover .step-number { transform: scale(1.15); }

        /* Marquee testimonials */
        .testimonial-track {
            display: flex;
            gap: 1.5rem;
            animation: marquee 40s linear infinite;
            width: max-content;
            padding: 0.5rem 0;
        }
        .testimonial-track:hover { animation-play-state: paused; }
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-50%); }
        }
        .testimonial-card {
            flex-shrink: 0;
            width: 360px;
            border-radius: 1.5rem;
            padding: 1.75rem;
            background: rgba(255,255,255,0.6);
            backdrop-filter: blur(16px);
            border: 1px solid rgba(255,255,255,0.4);
            box-shadow: 0 4px 24px rgba(0,0,0,0.03);
            transition: all 0.3s;
        }
        .testimonial-card:hover {
            background: rgba(255,255,255,0.8);
            box-shadow: 0 12px 40px rgba(99,102,241,0.08);
            transform: translateY(-2px);
        }

        /* Reveal animations */
        .reveal, .reveal-left, .reveal-right, .reveal-scale {
            opacity: 0;
            transition: all 0.8s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .reveal { transform: translateY(50px); }
        .reveal-left { transform: translateX(-50px); }
        .reveal-right { transform: translateX(50px); }
        .reveal-scale { transform: scale(0.92); }
        .reveal.active, .reveal-left.active, .reveal-right.active, .reveal-scale.active {
            opacity: 1;
            transform: translate(0) scale(1);
        }

        /* CTA gradient sweep */
        .cta-card {
            position: relative;
            overflow: hidden;
            border-radius: 2.5rem;
            background: linear-gradient(135deg, #1e1b4b, #312e81, #581c87, #831843);
            background-size: 400% 400%;
            animation: ctaGradient 12s ease infinite;
        }
        @keyframes ctaGradient {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }
        .cta-card::before {
            content: '';
            position: absolute;
            inset: 0;
            background: radial-gradient(ellipse 80% 50% at 50% 0%, rgba(255,255,255,0.08) 0%, transparent 60%);
        }
        .cta-sweep {
            position: absolute;
            inset: 0;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.06), transparent);
            transform: skewX(-12deg) translateX(-100%);
            transition: transform 0.8s;
        }
        .cta-card:hover .cta-sweep { transform: skewX(-12deg) translateX(200%); }

        /* FAQ */
        .faq-answer {
            max-height: 0;
            overflow: hidden;
            transition: max-height 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .faq-answer.open { max-height: 300px; }
        .faq-toggle .fa-chevron-down { transition: transform 0.4s; }

        /* Counter */
        .counter { font-variant-numeric: tabular-nums; }

        /* Nav */
        .nav-link {
            position: relative;
            color: #4b5563;
            font-weight: 500;
            font-size: 0.9rem;
            transition: color 0.3s;
        }
        .nav-link::after {
            content: '';
            position: absolute;
            bottom: -2px; left: 0;
            width: 0; height: 2px;
            background: linear-gradient(90deg, #6366f1, #a855f7);
            transition: width 0.3s;
            border-radius: 1px;
        }
        .nav-link:hover { color: #4f46e5; }
        .nav-link:hover::after { width: 100%; }

        /* Mobile menu */
        .mobile-menu {
            position: fixed;
            inset: 0;
            z-index: 100;
            transform: translateX(-100%);
            transition: transform 0.5s cubic-bezier(0.23, 1, 0.32, 1);
        }
        .mobile-menu.open { transform: translateX(0); }

        /* Back to top */
        .back-to-top {
            position: fixed;
            bottom: 2rem; right: 2rem;
            z-index: 50;
            width: 48px; height: 48px;
            border-radius: 50%;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 8px 30px rgba(99,102,241,0.3);
            opacity: 0;
            pointer-events: none;
            transform: translateY(20px);
            transition: all 0.4s cubic-bezier(0.23, 1, 0.32, 1);
            cursor: pointer;
        }
        .back-to-top.visible { opacity: 1; pointer-events: auto; transform: translateY(0); }
        .back-to-top:hover { transform: translateY(-3px) scale(1.05); box-shadow: 0 12px 40px rgba(99,102,241,0.4); }

        /* Typing animation */
        .typing-text {
            display: inline-block;
            overflow: hidden;
            white-space: nowrap;
            border-right: 3px solid #6366f1;
            animation: typing 3.5s steps(30) 1s forwards, blink 0.8s step-end infinite;
            max-width: fit-content;
        }
        @keyframes typing {
            0% { width: 0; }
            100% { width: 100%; }
        }
        @keyframes blink {
            50% { border-color: transparent; }
        }

        /* Particles */
        .particle {
            position: absolute;
            border-radius: 50%;
            pointer-events: none;
            animation: particleFloat 15s infinite linear;
        }
        @keyframes particleFloat {
            0% { transform: translateY(100vh) rotate(0deg); opacity: 0; }
            10% { opacity: 0.4; }
            90% { opacity: 0.2; }
            100% { transform: translateY(-20vh) rotate(360deg); opacity: 0; }
        }

        @media (max-width: 640px) {
            .typing-text { white-space: normal; border-right: none; animation: none; }
            .mockup { transform: none; }
            .testimonial-card { width: 300px; }
        }
    </style>
</head>
<body>
    <!-- Background -->
    <div class="orb"></div><div class="orb"></div><div class="orb"></div>
    <div class="mesh-bg"></div>
    <div id="particles" class="fixed inset-0 z-0 pointer-events-none"></div>

    <!-- Navigation -->
    <nav class="fixed top-5 left-1/2 -translate-x-1/2 w-[92%] md:w-[85%] max-w-6xl z-50 glass-card-strong px-5 md:px-8 py-3">
        <div class="flex items-center justify-between">
            <a href="#hero" class="flex items-center gap-3">
                <div class="w-10 h-10 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-xl flex items-center justify-center shadow-lg shadow-indigo-200">
                    <i class="fas fa-brain text-white text-lg"></i>
                </div>
                <span class="font-bold text-lg text-transparent bg-clip-text bg-gradient-to-r from-indigo-700 to-purple-700 hidden sm:block" style="font-family: 'Space Grotesk', sans-serif;">Study Assistant</span>
            </a>
            <div class="hidden md:flex items-center gap-8">
                <a href="#features" class="nav-link">Features</a>
                <a href="#how-it-works" class="nav-link">How it works</a>
                <a href="#testimonials" class="nav-link">Testimonials</a>
                <a href="#faq" class="nav-link">FAQ</a>
            </div>
            <div class="hidden md:flex items-center gap-3">
                <a href="/login" class="px-5 py-2 text-sm font-semibold text-indigo-600 hover:text-indigo-800 transition">Sign in</a>
                <a href="/register" class="animated-border">
                    <span class="block px-5 py-2 text-sm font-semibold text-white bg-gray-900 rounded-full hover:bg-gray-800 transition-colors">Get started</span>
                </a>
            </div>
            <button id="menuToggle" class="md:hidden text-gray-600 text-2xl focus:outline-none p-1" aria-label="Toggle menu">
                <i class="fas fa-bars"></i>
            </button>
        </div>
    </nav>

    <!-- Mobile Menu -->
    <div id="mobileMenu" class="mobile-menu">
        <div class="absolute inset-0 bg-black/20 backdrop-blur-sm" id="menuOverlay"></div>
        <div class="relative w-72 h-full bg-white/95 backdrop-blur-xl shadow-2xl p-8 pt-28">
            <button id="menuClose" class="absolute top-8 right-6 text-gray-400 hover:text-gray-600 text-2xl transition" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
            <div class="flex flex-col gap-5">
                <a href="#features" class="text-gray-700 hover:text-indigo-600 text-lg font-medium transition mobile-link">Features</a>
                <a href="#how-it-works" class="text-gray-700 hover:text-indigo-600 text-lg font-medium transition mobile-link">How it works</a>
                <a href="#testimonials" class="text-gray-700 hover:text-indigo-600 text-lg font-medium transition mobile-link">Testimonials</a>
                <a href="#faq" class="text-gray-700 hover:text-indigo-600 text-lg font-medium transition mobile-link">FAQ</a>
                <hr class="border-gray-100 my-2">
                <a href="/login" class="text-gray-700 hover:text-indigo-600 text-lg font-medium transition">Sign in</a>
                <a href="/register" class="inline-flex items-center justify-center px-6 py-3 text-white bg-gradient-to-r from-indigo-600 to-purple-600 rounded-full font-semibold shadow-lg mt-2">Get started free</a>
            </div>
        </div>
    </div>

    <!-- Hero Section -->
    <section id="hero" class="relative z-10 pt-36 pb-24 md:pt-48 md:pb-36 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="grid md:grid-cols-2 gap-12 lg:gap-20 items-center">
                <div class="reveal">
                    <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-white/60 backdrop-blur-sm border border-indigo-200/50 text-indigo-700 text-sm font-semibold mb-6 shadow-sm">
                        <span class="w-2 h-2 rounded-full bg-indigo-500 animate-pulse"></span>
                        AI-powered learning companion
                    </div>
                    <h1 class="text-4xl sm:text-5xl lg:text-7xl font-extrabold tracking-tight leading-[1.1] text-gray-900">
                        Your notes,
                        <span class="text-glow">supercharged</span>
                        <span class="block text-gray-800">with AI</span>
                    </h1>
                    <p class="mt-6 text-lg sm:text-xl text-gray-500 max-w-lg leading-relaxed">
                        Upload PDFs, slides, handwritten notes — then chat, generate quizzes, flashcards, study plans, track time, and master your subjects faster.
                    </p>
                    <div class="mt-8 flex flex-col sm:flex-row gap-4">
                        <a href="/register" class="group inline-flex items-center justify-center gap-2 px-7 py-3.5 text-base font-bold rounded-full text-white bg-gray-900 hover:bg-gray-800 shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-0.5">
                            Get started free
                            <i class="fas fa-arrow-right text-sm group-hover:translate-x-1 transition-transform"></i>
                        </a>
                        <a href="#how-it-works" class="inline-flex items-center justify-center gap-2 px-7 py-3.5 text-base font-semibold rounded-full bg-white/70 backdrop-blur-sm border border-gray-200 text-gray-700 hover:bg-white hover:border-indigo-300 transition-all duration-300">
                            <i class="fas fa-play-circle text-indigo-500"></i>
                            See how it works
                        </a>
                    </div>
                    <div class="mt-8 flex flex-wrap items-center gap-5 text-sm text-gray-400">
                        <span class="flex items-center gap-1.5"><i class="fas fa-check-circle text-emerald-500"></i> No credit card</span>
                        <span class="flex items-center gap-1.5"><i class="fas fa-check-circle text-emerald-500"></i> Free for students</span>
                        <span class="flex items-center gap-1.5"><i class="fas fa-check-circle text-emerald-500"></i> Privacy first</span>
                    </div>
                </div>

                <!-- Hero Mockup -->
                <div class="relative reveal" style="transition-delay: 0.15s;">
                    <div class="mockup bg-white">
                        <div class="mockup-glow"></div>
                        <div class="p-5">
                            <div class="flex items-center justify-between mb-5">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-lg bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center shadow-md">
                                        <i class="fas fa-robot text-white text-sm"></i>
                                    </div>
                                    <div>
                                        <div class="text-sm font-semibold text-gray-800">AI Assistant</div>
                                        <div class="text-xs text-gray-400">Online</div>
                                    </div>
                                </div>
                                <div class="flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                    <span class="text-xs text-emerald-600 font-medium">Active</span>
                                </div>
                            </div>
                            <div class="space-y-3">
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fas fa-user text-indigo-600 text-xs"></i>
                                    </div>
                                    <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm text-gray-700">
                                        Explain DBMS normalization from my notes.
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-gradient-to-br from-indigo-500 to-purple-500 flex items-center justify-center flex-shrink-0 mt-0.5 shadow-md">
                                        <i class="fas fa-robot text-white text-xs"></i>
                                    </div>
                                    <div class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm text-gray-800 border-l-2 border-indigo-400 shadow-sm">
                                        Based on your slides: Normalization removes redundancy using 1NF, 2NF, 3NF...
                                    </div>
                                </div>
                                <div class="flex items-start gap-2.5">
                                    <div class="w-8 h-8 rounded-full bg-indigo-100 flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <i class="fas fa-user text-indigo-600 text-xs"></i>
                                    </div>
                                    <div class="bg-gray-100 rounded-2xl rounded-tl-sm px-4 py-2.5 text-sm text-gray-700">
                                        Generate MCQ from Unit 4.
                                    </div>
                                </div>
                                <div class="flex items-center justify-between pt-1">
                                    <div class="flex items-center gap-1.5 text-xs text-indigo-600 bg-indigo-50 rounded-full px-3 py-1.5">
                                        <i class="fas fa-file-alt text-xs"></i>
                                        <span>3 sources cited</span>
                                    </div>
                                    <div class="flex -space-x-2">
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 border-2 border-white flex items-center justify-center text-[10px] font-bold text-white shadow-sm">A</div>
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-purple-400 to-purple-600 border-2 border-white flex items-center justify-center text-[10px] font-bold text-white shadow-sm">B</div>
                                        <div class="w-7 h-7 rounded-full bg-gradient-to-br from-pink-400 to-pink-600 border-2 border-white flex items-center justify-center text-[10px] font-bold text-white shadow-sm">C</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="absolute -top-8 -right-8 w-40 h-40 bg-purple-400/20 rounded-full blur-3xl -z-10 animate-pulse"></div>
                    <div class="absolute -bottom-8 -left-8 w-40 h-40 bg-indigo-400/20 rounded-full blur-3xl -z-10 animate-pulse" style="animation-delay: 2s;"></div>
                </div>
            </div>
        </div>
    </section>

    <!-- Stats Banner -->
    <section class="relative z-10 pb-8 px-4">
        <div class="max-w-5xl mx-auto reveal-scale">
            <div class="glass-card-strong rounded-2xl p-8 md:p-12">
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
                    <div>
                        <div class="text-4xl md:text-5xl font-black text-indigo-600 counter font-[Space_Grotesk]" data-target="10">0</div>
                        <div class="text-gray-500 text-sm font-medium mt-1">Active students <span class="text-indigo-400">(k+)</span></div>
                    </div>
                    <div class="sm:border-x border-gray-200/60">
                        <div class="text-4xl md:text-5xl font-black text-purple-600 counter font-[Space_Grotesk]" data-target="50">0</div>
                        <div class="text-gray-500 text-sm font-medium mt-1">Notes processed <span class="text-purple-400">(k+)</span></div>
                    </div>
                    <div>
                        <div class="text-4xl md:text-5xl font-black text-pink-600 counter font-[Space_Grotesk]" data-target="98">0</div>
                        <div class="text-gray-500 text-sm font-medium mt-1">Success rate</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Features -->
    <section id="features" class="relative z-10 py-20 md:py-28 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 max-w-2xl mx-auto reveal">
                <span class="text-indigo-600 font-semibold tracking-wider uppercase text-xs">Features</span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mt-3 leading-tight">
                    Everything you need to
                    <span class="text-glow">study smarter</span>
                </h2>
                <p class="text-gray-500 text-lg mt-4">Upload once, ask anything — AI that knows only your materials.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 md:gap-6">
                <div class="feature-card reveal" style="transition-delay: 0s;">
                    <div class="feature-icon bg-gradient-to-br from-indigo-500 to-purple-600 shadow-lg shadow-indigo-200">
                        <i class="fas fa-cloud-upload-alt"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">Smart uploads</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">PDF, PPT, images, handwritten notes — OCR extracts text automatically. Drag & drop with progress.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.07s;">
                    <div class="feature-icon bg-gradient-to-br from-purple-500 to-pink-600 shadow-lg shadow-purple-200">
                        <i class="fas fa-comment-dots"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">AI chat</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Ask questions, get answers with citations from your notes. Full conversation history & markdown.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.14s;">
                    <div class="feature-icon bg-gradient-to-br from-blue-500 to-cyan-500 shadow-lg shadow-blue-200">
                        <i class="fas fa-question-circle"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">Quiz generator</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Auto‑generate MCQs, true/false, short answer, fill‑blank, matching & flashcards from your materials.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.21s;">
                    <div class="feature-icon bg-gradient-to-br from-emerald-500 to-green-600 shadow-lg shadow-emerald-200">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">Study plans & exams</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Plan your studies with milestones, track exam dates on a calendar, and take timed quiz attempts.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.28s;">
                    <div class="feature-icon bg-gradient-to-br from-orange-500 to-red-500 shadow-lg shadow-orange-200">
                        <i class="fas fa-clock"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">Pomodoro & time tracking</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Stay focused with the Pomodoro timer and log study hours per subject to see where your time goes.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.35s;">
                    <div class="feature-icon bg-gradient-to-br from-teal-500 to-cyan-600 shadow-lg shadow-teal-200">
                        <i class="fas fa-layer-group"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">Flashcards & spaced repetition</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Review flashcards with spaced repetition scheduling. Optimise retention with smart review intervals.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.07s;">
                    <div class="feature-icon bg-gradient-to-br from-pink-500 to-rose-600 shadow-lg shadow-pink-200">
                        <i class="fas fa-users"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">Study groups & peer review</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Collaborate with classmates, share question banks, and review each other's content in study groups.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.14s;">
                    <div class="feature-icon bg-gradient-to-br from-violet-500 to-indigo-600 shadow-lg shadow-violet-200">
                        <i class="fas fa-bookmark"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">Bookmarks & export</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Bookmark important questions and export your study materials to PDF, CSV, or Anki flashcards.</p>
                </div>
                <div class="feature-card reveal" style="transition-delay: 0.21s;">
                    <div class="feature-icon bg-gradient-to-br from-sky-500 to-blue-600 shadow-lg shadow-sky-200">
                        <i class="fas fa-shield-alt"></i>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900 mt-5">Privacy first</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Your data stays yours. AI only accesses materials you upload. No training on your content. Ever.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section id="how-it-works" class="relative z-10 py-20 md:py-28 px-4">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-16 max-w-2xl mx-auto reveal">
                <span class="text-indigo-600 font-semibold tracking-wider uppercase text-xs">How it works</span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mt-3 leading-tight">
                    Four steps to
                    <span class="text-glow">smarter learning</span>
                </h2>
                <p class="text-gray-500 text-lg mt-4">Get started in minutes — no setup required.</p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-5">
                <div class="step-card reveal">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white text-xl shadow-lg shadow-indigo-200">
                            <i class="fas fa-cloud-upload-alt"></i>
                        </div>
                        <div class="step-number bg-indigo-100 text-indigo-700">1</div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Upload</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Drag & drop your PDFs, slides, or handwritten notes. OCR extracts every word automatically.</p>
                </div>
                <div class="step-card reveal" style="transition-delay: 0.1s;">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-purple-500 to-pink-600 flex items-center justify-center text-white text-xl shadow-lg shadow-purple-200">
                            <i class="fas fa-microchip"></i>
                        </div>
                        <div class="step-number bg-purple-100 text-purple-700">2</div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Process</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">AI analyses your materials, creates searchable chunks, and builds embeddings for smart retrieval.</p>
                </div>
                <div class="step-card reveal" style="transition-delay: 0.2s;">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-cyan-600 flex items-center justify-center text-white text-xl shadow-lg shadow-blue-200">
                            <i class="fas fa-comments"></i>
                        </div>
                        <div class="step-number bg-blue-100 text-blue-700">3</div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Chat, Quiz & Study</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Chat with your notes, generate 6 types of quizzes, create flashcards, and plan your studies.</p>
                </div>
                <div class="step-card reveal" style="transition-delay: 0.3s;">
                    <div class="flex items-center gap-4 mb-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-emerald-500 to-green-600 flex items-center justify-center text-white text-xl shadow-lg shadow-emerald-200">
                            <i class="fas fa-graduation-cap"></i>
                        </div>
                        <div class="step-number bg-emerald-100 text-emerald-700">4</div>
                    </div>
                    <h3 class="text-lg font-bold text-gray-900">Master</h3>
                    <p class="text-gray-500 text-sm mt-2 leading-relaxed">Take timed quizzes, track time, review with spaced repetition — ace your exams with confidence.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section id="testimonials" class="relative z-10 py-20 md:py-28 px-4 overflow-hidden">
        <div class="max-w-7xl mx-auto">
            <div class="text-center mb-12 max-w-2xl mx-auto reveal">
                <span class="text-indigo-600 font-semibold tracking-wider uppercase text-xs">Testimonials</span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mt-3 leading-tight">
                    Loved by
                    <span class="text-glow">students</span>
                </h2>
                <p class="text-gray-500 text-lg mt-4">Join thousands who study smarter with AI.</p>
            </div>
            <div class="relative">
                <div class="absolute left-0 top-0 bottom-0 w-16 bg-gradient-to-r from-[#f8fafc] to-transparent z-10 pointer-events-none"></div>
                <div class="absolute right-0 top-0 bottom-0 w-16 bg-gradient-to-l from-[#f8fafc] to-transparent z-10 pointer-events-none"></div>
                <div class="testimonial-track">
                    @php
                        $testimonials = [
                            ['name' => 'Priya S.', 'role' => 'Computer Science', 'avatar' => 'P', 'gradient' => 'from-indigo-500 to-purple-600', 'text' => 'This tool saved my semester! I uploaded all my DBMS notes and the AI answered every query perfectly. The MCQ generator is a game-changer.'],
                            ['name' => 'James M.', 'role' => 'Engineering', 'avatar' => 'J', 'gradient' => 'from-purple-500 to-pink-600', 'text' => 'The way it cites sources from my own PDFs is incredible. No more searching through 200 pages for that one concept.'],
                            ['name' => 'Aisha K.', 'role' => 'Medicine', 'avatar' => 'A', 'gradient' => 'from-pink-500 to-rose-600', 'text' => 'OCR on handwritten notes works flawlessly. I scanned all my lecture notes and the AI understands everything. Highly recommend!'],
                            ['name' => 'Raj P.', 'role' => 'Data Science', 'avatar' => 'R', 'gradient' => 'from-blue-500 to-cyan-600', 'text' => 'I use it daily for revision. The chat remembers context across sessions. It feels like having a personal tutor 24/7.'],
                            ['name' => 'Sarah L.', 'role' => 'Business', 'avatar' => 'S', 'gradient' => 'from-emerald-500 to-green-600', 'text' => 'Finally an AI tool that actually respects privacy. My notes are mine, the AI just helps me understand them better. Brilliant.'],
                            ['name' => 'Carlos G.', 'role' => 'Languages', 'avatar' => 'C', 'gradient' => 'from-orange-500 to-red-600', 'text' => 'Uploaded 30+ documents and it processes everything fast. The subject organization keeps everything tidy. Love it!'],
                        ];
                    @endphp
                    @foreach($testimonials as $t)
                    <div class="testimonial-card">
                        <div class="flex items-center gap-1 text-amber-400 text-sm mb-3">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed mb-5">"{{ $t['text'] }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $t['gradient'] }} flex items-center justify-center text-white font-bold text-sm shadow-md">{{ $t['avatar'] }}</div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $t['name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $t['role'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                    @foreach($testimonials as $t)
                    <div class="testimonial-card">
                        <div class="flex items-center gap-1 text-amber-400 text-sm mb-3">
                            <i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i><i class="fas fa-star"></i>
                        </div>
                        <p class="text-gray-600 text-sm leading-relaxed mb-5">"{{ $t['text'] }}"</p>
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br {{ $t['gradient'] }} flex items-center justify-center text-white font-bold text-sm shadow-md">{{ $t['avatar'] }}</div>
                            <div>
                                <div class="text-sm font-bold text-gray-900">{{ $t['name'] }}</div>
                                <div class="text-xs text-gray-400">{{ $t['role'] }}</div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="relative z-10 py-20 md:py-28 px-4">
        <div class="max-w-3xl mx-auto">
            <div class="text-center mb-12 max-w-xl mx-auto reveal">
                <span class="text-indigo-600 font-semibold tracking-wider uppercase text-xs">FAQ</span>
                <h2 class="text-3xl sm:text-4xl md:text-5xl font-bold text-gray-900 mt-3 leading-tight">
                    Got questions?
                    <span class="text-glow">We've got answers</span>
                </h2>
            </div>
            <div class="space-y-3 reveal">
                @php
                    $faqs = [
                        ['q' => 'What file formats are supported?', 'a' => 'We support PDF, DOCX, PPTX, TXT, and image files (JPG, PNG) with OCR. Most common academic formats are covered.'],
                        ['q' => 'Is my data private and secure?', 'a' => 'Absolutely. Your uploaded documents are stored securely and are only accessible to you. We never train AI models on your content. Your data stays yours.'],
                        ['q' => 'How does the AI chat work?', 'a' => 'When you ask a question, the AI searches through your uploaded materials to find relevant information. It answers based only on your notes, with citations to the source documents.'],
                        ['q' => 'What quiz types can I generate?', 'a' => 'Six types: multiple-choice, true/false, short answer, fill-in-the-blank, matching, and flashcards. Each can be auto-generated from your notes with answer keys.'],
                        ['q' => 'Can I study with others?', 'a' => 'Yes! Create study groups, share question banks with classmates, and peer-review each other\'s content. Collaborative learning made easy.'],
                        ['q' => 'How does the Pomodoro timer help?', 'a' => 'Stay focused with timed study sessions, track your hours per subject, and see your productivity trends over time. Perfect for exam prep.'],
                        ['q' => 'Is there a limit on how many documents I can upload?', 'a' => 'Free tier includes generous limits for students. We believe in making learning accessible — no hidden caps or surprise billing.'],
                        ['q' => 'Do I need a credit card to sign up?', 'a' => 'Not at all. Our student plan is completely free with no credit card required. Just sign up and start uploading your notes.'],
                    ];
                @endphp
                @foreach($faqs as $faq)
                <div class="bg-white/50 backdrop-blur-sm rounded-2xl border border-white/40 shadow-sm hover:shadow-md transition-all duration-300">
                    <button class="faq-toggle w-full flex items-center justify-between p-5 md:p-6 text-left focus:outline-none group">
                        <span class="font-semibold text-gray-800 text-sm md:text-base pr-4 group-hover:text-indigo-600 transition-colors">{{ $faq['q'] }}</span>
                        <i class="fas fa-chevron-down text-indigo-400 flex-shrink-0"></i>
                    </button>
                    <div class="faq-answer px-5 md:px-6">
                        <p class="text-gray-500 text-sm leading-relaxed pb-5 md:pb-6">{{ $faq['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="relative z-10 py-16 md:py-24 px-4">
        <div class="max-w-4xl mx-auto reveal-scale">
            <div class="cta-card p-8 sm:p-12 md:p-16 text-center">
                <div class="cta-sweep"></div>
                <div class="relative">
                    <h2 class="text-2xl sm:text-3xl md:text-5xl font-bold text-white mb-4 leading-tight">Ready to revolutionise your learning?</h2>
                    <p class="text-indigo-200 text-base md:text-lg mb-8 max-w-md mx-auto">Join thousands of students who study with their personal AI assistant.</p>
                    <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                        <a href="/register" class="animated-border">
                            <span class="block px-8 py-3.5 text-base font-bold rounded-full text-gray-900 bg-white hover:bg-gray-50 transition-colors shadow-xl">
                                <i class="fas fa-sparkles mr-2"></i> Sign up free
                            </span>
                        </a>
                        <a href="/login" class="inline-flex items-center gap-2 px-8 py-3.5 text-base font-semibold rounded-full bg-white/10 backdrop-blur-sm border border-white/20 text-white hover:bg-white/20 transition-all duration-300">
                            <i class="fas fa-arrow-right"></i> Sign in
                        </a>
                    </div>
                    <p class="text-indigo-300/80 text-sm mt-8">✨ Free forever for students · No hidden fees · Privacy guaranteed ✨</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="relative z-10 border-t border-gray-200/50 bg-white/40 backdrop-blur-sm">
        <div class="max-w-7xl mx-auto px-4 md:px-8 py-12">
            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 pb-8 border-b border-gray-200/40">
                <div class="sm:col-span-2 md:col-span-1">
                    <div class="flex items-center gap-3 mb-4">
                        <div class="w-9 h-9 bg-gradient-to-br from-indigo-600 to-purple-600 rounded-lg flex items-center justify-center shadow-md">
                            <i class="fas fa-brain text-white text-sm"></i>
                        </div>
                        <span class="font-bold text-lg text-transparent bg-clip-text bg-gradient-to-r from-indigo-700 to-purple-700" style="font-family: 'Space Grotesk', sans-serif;">Study Assistant</span>
                    </div>
                    <p class="text-gray-500 text-sm leading-relaxed max-w-xs">Empowering students with AI-powered study tools. Upload, chat, quiz — and master any subject.</p>
                    <div class="flex gap-3 mt-5">
                        <a href="#" class="w-9 h-9 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-600 hover:bg-indigo-100 transition text-sm" aria-label="Twitter"><i class="fab fa-twitter"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-purple-50 flex items-center justify-center text-purple-600 hover:bg-purple-100 transition text-sm" aria-label="GitHub"><i class="fab fa-github"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-pink-50 flex items-center justify-center text-pink-600 hover:bg-pink-100 transition text-sm" aria-label="LinkedIn"><i class="fab fa-linkedin-in"></i></a>
                        <a href="#" class="w-9 h-9 rounded-full bg-blue-50 flex items-center justify-center text-blue-600 hover:bg-blue-100 transition text-sm" aria-label="YouTube"><i class="fab fa-youtube"></i></a>
                    </div>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 text-sm mb-4">Product</h4>
                    <ul class="space-y-3">
                        <li><a href="#features" class="text-gray-500 hover:text-indigo-600 transition text-sm">Features</a></li>
                        <li><a href="#how-it-works" class="text-gray-500 hover:text-indigo-600 transition text-sm">How it works</a></li>
                        <li><a href="#testimonials" class="text-gray-500 hover:text-indigo-600 transition text-sm">Testimonials</a></li>
                        <li><a href="#faq" class="text-gray-500 hover:text-indigo-600 transition text-sm">FAQ</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 text-sm mb-4">Company</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition text-sm">About</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition text-sm">Blog</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition text-sm">Careers</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition text-sm">Contact</a></li>
                    </ul>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800 text-sm mb-4">Legal</h4>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition text-sm">Privacy Policy</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition text-sm">Terms of Service</a></li>
                        <li><a href="#" class="text-gray-500 hover:text-indigo-600 transition text-sm">Cookie Policy</a></li>
                    </ul>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-center justify-between pt-6 text-gray-400 text-xs">
                <p>&copy; {{ date('Y') }} Study Assistant for Students. Built with <i class="fas fa-heart text-red-400"></i> for students.</p>
                <p class="mt-2 sm:mt-0">Powered by Champ & AI</p>
            </div>
        </div>
    </footer>

    <!-- Back to Top -->
    <button class="back-to-top" id="backToTop" aria-label="Back to top">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script>
        // Particles
        (function() {
            const c = document.getElementById('particles');
            if (!c) return;
            for (let i = 0; i < 40; i++) {
                const p = document.createElement('div');
                p.classList.add('particle');
                const s = Math.random() * 5 + 2;
                p.style.cssText = `width:${s}px;height:${s}px;left:${Math.random()*100}%;animation-duration:${Math.random()*12+10}s;animation-delay:${Math.random()*5}s;background:radial-gradient(circle,rgba(${99+Math.random()*40},${102+Math.random()*40},241,0.4) 0%,rgba(139,92,246,0) 80%)`;
                c.appendChild(p);
            }
        })();

        // Mobile menu
        const menuToggle = document.getElementById('menuToggle');
        const menuClose = document.getElementById('menuClose');
        const menuOverlay = document.getElementById('menuOverlay');
        const mobileMenu = document.getElementById('mobileMenu');
        const mobileLinks = document.querySelectorAll('.mobile-link');

        function openMenu() { mobileMenu.classList.add('open'); document.body.style.overflow = 'hidden'; }
        function closeMenu() { mobileMenu.classList.remove('open'); document.body.style.overflow = ''; }

        if (menuToggle) menuToggle.addEventListener('click', openMenu);
        if (menuClose) menuClose.addEventListener('click', closeMenu);
        if (menuOverlay) menuOverlay.addEventListener('click', closeMenu);
        mobileLinks.forEach(l => l.addEventListener('click', closeMenu));

        // Scroll reveal
        (function() {
            const els = document.querySelectorAll('.reveal, .reveal-left, .reveal-right, .reveal-scale');
            if (!els.length) return;
            const ob = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (e.isIntersecting) { e.target.classList.add('active'); ob.unobserve(e.target); }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -40px 0px' });
            els.forEach(el => ob.observe(el));
        })();

        // Counters
        (function() {
            const counters = document.querySelectorAll('.counter');
            if (!counters.length) return;
            const ob = new IntersectionObserver((entries) => {
                entries.forEach(e => {
                    if (!e.isIntersecting) return;
                    const el = e.target;
                    const target = parseInt(el.dataset.target);
                    if (isNaN(target)) return;
                    const suffix = target >= 98 ? '%' : '';
                    let current = 0;
                    const step = Math.ceil(target / 50);
                    const interval = setInterval(() => {
                        current += step;
                        if (current >= target) { current = target; clearInterval(interval); }
                        el.textContent = current + suffix;
                    }, 30);
                    ob.unobserve(el);
                });
            }, { threshold: 0.5 });
            counters.forEach(c => ob.observe(c));
        })();

        // FAQ
        document.querySelectorAll('.faq-toggle').forEach(btn => {
            btn.addEventListener('click', function() {
                const answer = this.nextElementSibling;
                const icon = this.querySelector('.fa-chevron-down');
                const isOpen = answer.classList.contains('open');
                document.querySelectorAll('.faq-answer.open').forEach(a => {
                    a.classList.remove('open');
                    a.previousElementSibling.querySelector('.fa-chevron-down').style.transform = 'rotate(0deg)';
                });
                if (!isOpen) {
                    answer.classList.add('open');
                    icon.style.transform = 'rotate(180deg)';
                }
            });
        });

        // Back to top
        const backToTop = document.getElementById('backToTop');
        if (backToTop) {
            window.addEventListener('scroll', () => backToTop.classList.toggle('visible', window.scrollY > 400));
            backToTop.addEventListener('click', () => window.scrollTo({ top: 0, behavior: 'smooth' }));
        }
    </script>
</body>
</html>
