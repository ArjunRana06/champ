<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Trip Planner Nepal · AI Travel Designer</title>

    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        'forest': '#0A3A2A',
                        'gold': '#F5B042',
                        'cream': '#FFF9F0',
                    },
                    fontFamily: {
                        'sans': ['Inter', 'system-ui', 'sans-serif'],
                        'display': ['Poppins', 'sans-serif'],
                        'nepali': ['Mukta', 'sans-serif'],
                    },
                    animation: {
                        'float': 'float 4s ease-in-out infinite',
                        'glow': 'glow 2s ease-in-out infinite alternate',
                    },
                    keyframes: {
                        float: {
                            '0%,100%': {
                                transform: 'translateY(0)'
                            },
                            '50%': {
                                transform: 'translateY(-10px)'
                            }
                        },
                        glow: {
                            '0%': {
                                textShadow: '0 0 5px rgba(245,176,66,0.3)'
                            },
                            '100%': {
                                textShadow: '0 0 20px rgba(245,176,66,0.8)'
                            }
                        },
                    },
                },
            },
        }
    </script>

    <!-- Fonts -->
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Poppins:wght@700;800;900&family=Mukta:wght@300;400;500;600;700&display=swap"
        rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <!-- GSAP -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/gsap.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/gsap/3.12.5/ScrollTrigger.min.js"></script>
    <!-- Three.js -->
    <script type="importmap">
        { "imports": { "three": "https://unpkg.com/three@0.128.0/build/three.module.js" } }
    </script>

    <style>
        /* Custom cursor */
        .custom-cursor {
            position: fixed;
            width: 12px;
            height: 12px;
            background: #F5B042;
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999;
            transform: translate(-50%, -50%);
            mix-blend-mode: difference;
            transition: transform 0.1s;
        }

        /* Glass */
        .glass {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .glass-dark {
            background: rgba(10, 58, 42, 0.6);
            backdrop-filter: blur(12px);
            border: 1px solid rgba(245, 176, 66, 0.3);
        }

        /* Tilt cards */
        .tilt-card {
            transition: transform 0.3s ease;
        }

        .tilt-card:hover {
            transform: rotateX(3deg) rotateY(3deg) translateY(-5px);
        }

        /* Scroll reveal */
        .reveal {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.8s cubic-bezier(0.2, 0.9, 0.4, 1.1);
        }

        .reveal.active {
            opacity: 1;
            transform: translateY(0);
        }

        /* Shimmer text */
        .shimmer {
            background: linear-gradient(90deg, #F5B042, #FFD966, #F5B042);
            background-size: 200% auto;
            -webkit-background-clip: text;
            background-clip: text;
            color: transparent;
            animation: shimmer 3s linear infinite;
        }

        @keyframes shimmer {
            0% {
                background-position: -200% 0;
            }

            100% {
                background-position: 200% 0;
            }
        }

        /* Marquee */
        /* Marquee for testimonials */
        .marquee {
            overflow: hidden;
            position: relative;
            width: 100%;
        }

        .marquee-content {
            display: flex;
            gap: 2rem;
            animation: marquee 30s linear infinite;
            width: max-content;
        }

        @keyframes marquee {
            0% {
                transform: translateX(0);
            }

            100% {
                transform: translateX(-50%);
            }
        }

        /* Testimonial card – ensures text wraps */
        .testimonial-card {
            width: 24rem;
            /* fixed width, adjust as needed */
            flex-shrink: 0;
            white-space: normal;
            word-break: break-word;
        }

        html {
            scroll-behavior: smooth;
        }
    </style>
</head>

<body class="bg-cream text-slate antialiased overflow-x-hidden" x-data="{
    lang: 'en',
    mobileMenuOpen: false,
    scrollY: 0,
    showCursor: false,
    init() {
        const savedLang = localStorage.getItem('lang');
        if (savedLang) this.lang = savedLang;
        const cursor = this.$refs.cursor;
        if (cursor) {
            document.addEventListener('mousemove', (e) => {
                cursor.style.left = e.clientX + 'px';
                cursor.style.top = e.clientY + 'px';
            });
            this.showCursor = true;
        }
        window.addEventListener('scroll', () => { this.scrollY = window.scrollY; });
        gsap.registerPlugin(ScrollTrigger);
        gsap.utils.toArray('.reveal').forEach(el => {
            ScrollTrigger.create({
                trigger: el,
                start: 'top 80%',
                onEnter: () => el.classList.add('active'),
                once: true,
            });
        });
    },
    toggleLang() {
        this.lang = this.lang === 'en' ? 'ne' : 'en';
        localStorage.setItem('lang', this.lang);
    }
}"
    :class="{ 'font-nepali': lang === 'ne', 'font-sans': lang === 'en' }" x-init="init()">

    <!-- Custom cursor -->
    <div class="custom-cursor hidden md:block" x-ref="cursor" x-show="showCursor"></div>

    @include('Frontend.partials.header')
    @include('Frontend.partials.navbar')

    <main>
        @yield('content')
    </main>

    @include('Frontend.partials.footer')

    <!-- Back to Top -->
    <button id="backToTop"
        class="fixed bottom-8 right-8 bg-gold text-forest p-4 rounded-full shadow-2xl hover:scale-110 transition-all z-50 opacity-0 invisible">
        <i class="fas fa-arrow-up"></i>
    </button>

    <script type="module">
        import * as THREE from 'https://unpkg.com/three@0.128.0/build/three.module.js';

        const canvas = document.getElementById('hero-canvas');
        if (canvas) {
            const scene = new THREE.Scene();
            const camera = new THREE.PerspectiveCamera(75, window.innerWidth / window.innerHeight, 0.1, 1000);
            const renderer = new THREE.WebGLRenderer({
                canvas,
                alpha: true
            });
            renderer.setSize(window.innerWidth, window.innerHeight);
            renderer.setPixelRatio(window.devicePixelRatio);

            const geometry = new THREE.BufferGeometry();
            const count = 2000;
            const positions = new Float32Array(count * 3);
            for (let i = 0; i < count; i++) {
                positions[i * 3] = (Math.random() - 0.5) * 200;
                positions[i * 3 + 1] = (Math.random() - 0.5) * 100;
                positions[i * 3 + 2] = (Math.random() - 0.5) * 100 - 50;
            }
            geometry.setAttribute('position', new THREE.BufferAttribute(positions, 3));
            const material = new THREE.PointsMaterial({
                color: 0xF5B042,
                size: 0.2,
                transparent: true,
                opacity: 0.5
            });
            const particles = new THREE.Points(geometry, material);
            scene.add(particles);
            camera.position.z = 80;

            function animate() {
                requestAnimationFrame(animate);
                particles.rotation.y += 0.002;
                particles.rotation.x += 0.001;
                renderer.render(scene, camera);
            }
            animate();

            window.addEventListener('resize', () => {
                camera.aspect = window.innerWidth / window.innerHeight;
                camera.updateProjectionMatrix();
                renderer.setSize(window.innerWidth, window.innerHeight);
            });
        }

        const backBtn = document.getElementById('backToTop');
        window.addEventListener('scroll', () => {
            if (window.scrollY > 300) {
                backBtn.classList.remove('opacity-0', 'invisible');
                backBtn.classList.add('opacity-100', 'visible');
            } else {
                backBtn.classList.add('opacity-0', 'invisible');
                backBtn.classList.remove('opacity-100', 'visible');
            }
        });
        backBtn.addEventListener('click', () => window.scrollTo({
            top: 0,
            behavior: 'smooth'
        }));
    </script>

    @stack('scripts')
</body>

</html>
