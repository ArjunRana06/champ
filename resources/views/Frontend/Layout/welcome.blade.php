<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
  <title>Life Replay — Rewind. Reflect. Relive. ✨</title>
  <!-- Tailwind CSS + Google Fonts + Font Awesome -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
  <style>
    * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
    body {
      background: radial-gradient(circle at 10% 20%, #eef9ff, #d9f0fa);
      overflow-x: hidden;
    }

    /* Animated gradient background that shifts */
    .animated-bg {
      position: fixed;
      top: 0;
      left: 0;
      width: 100%;
      height: 100%;
      background: linear-gradient(125deg, #e0f2fe, #bae6fd, #f0f9ff, #cffafe);
      background-size: 300% 300%;
      animation: gradientFlow 16s ease infinite;
      z-index: -2;
    }
    @keyframes gradientFlow {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }

    /* Floating orbs with glow */
    .glow-orb {
      position: absolute;
      border-radius: 50%;
      filter: blur(80px);
      opacity: 0.4;
      pointer-events: none;
      z-index: -1;
      animation: orbFloat 20s infinite alternate ease-in-out;
    }
    @keyframes orbFloat {
      0% { transform: translate(0, 0) scale(1); }
      100% { transform: translate(40px, -40px) scale(1.2); }
    }

    /* Dynamic cursor-following glow */
    .cursor-glow {
      position: fixed;
      width: 350px;
      height: 350px;
      background: radial-gradient(circle, rgba(56,189,248,0.25) 0%, rgba(56,189,248,0) 70%);
      border-radius: 50%;
      pointer-events: none;
      z-index: 9999;
      transform: translate(-50%, -50%);
      transition: transform 0.05s linear;
      will-change: transform;
    }

    /* Floating glass droplets (enhanced) */
    .droplet-premium {
      position: absolute;
      background: radial-gradient(circle, rgba(255,255,255,0.5), rgba(56,189,248,0.2));
      border-radius: 60% 40% 50% 50%;
      backdrop-filter: blur(4px);
      pointer-events: none;
      animation: floatDrop 14s infinite ease-in-out;
      z-index: -1;
      box-shadow: 0 8px 20px rgba(0,0,0,0.05);
    }
    @keyframes floatDrop {
      0% { transform: translateY(0) rotate(0deg); opacity: 0; }
      30% { opacity: 0.6; }
      100% { transform: translateY(-150px) rotate(20deg); opacity: 0; }
    }

    /* Memory sparkles */
    .sparkle {
      position: absolute;
      background: #fff;
      border-radius: 50%;
      filter: blur(2px);
      pointer-events: none;
      animation: sparkleTwinkle 3s infinite alternate;
    }
    @keyframes sparkleTwinkle {
      0% { opacity: 0; transform: scale(0); }
      100% { opacity: 1; transform: scale(1); }
    }

    /* Glass card (luxury style) */
    .glass-premium {
      background: rgba(255, 255, 255, 0.4);
      backdrop-filter: blur(14px);
      border: 1px solid rgba(255,255,255,0.6);
      box-shadow: 0 20px 35px -12px rgba(0, 0, 0, 0.1), 0 0 0 0.5px rgba(255,255,255,0.3);
      transition: all 0.35s cubic-bezier(0.2, 0.9, 0.4, 1.1);
    }
    .glass-premium:hover {
      background: rgba(255, 255, 255, 0.65);
      transform: translateY(-8px) scale(1.01);
      border-color: rgba(14, 165, 233, 0.5);
      box-shadow: 0 28px 40px -12px rgba(2, 132, 199, 0.3);
    }

    /* 3D flip on timeline cards (subtle) */
    .timeline-card {
      transition: all 0.4s ease;
      transform-style: preserve-3d;
    }
    .timeline-card:hover {
      transform: translateY(-8px) rotateX(2deg) rotateY(1deg);
    }

    /* Typewriter with neon glow */
    .typewriter-glow {
      text-shadow: 0 0 8px rgba(14,165,233,0.3);
    }

    /* Ripple button premium */
    .btn-ripple {
      position: relative;
      overflow: hidden;
      transition: all 0.3s;
    }
    .btn-ripple:after {
      content: "";
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      background: rgba(255,255,255,0.5);
      border-radius: 50%;
      transform: translate(-50%, -50%);
      transition: width 0.5s, height 0.5s;
    }
    .btn-ripple:active:after {
      width: 400px;
      height: 400px;
      opacity: 0;
      transition: 0s;
    }

    /* Pulse ring enhanced */
    .pulse-super {
      animation: superPulse 2.2s infinite;
    }
    @keyframes superPulse {
      0% { box-shadow: 0 0 0 0 rgba(14,165,233,0.6); }
      70% { box-shadow: 0 0 0 18px rgba(14,165,233,0); }
      100% { box-shadow: 0 0 0 0 rgba(14,165,233,0); }
    }

    /* Scroll reveal with zoom */
    .reveal-zoom {
      opacity: 0;
      transform: scale(0.95) translateY(30px);
      transition: opacity 0.7s cubic-bezier(0.2, 0.9, 0.3, 1.1), transform 0.7s ease;
    }
    .reveal-zoom.active {
      opacity: 1;
      transform: scale(1) translateY(0);
    }

    /* Chat bubble animations */
    .chat-glow {
      transition: all 0.2s;
    }

    /* Custom scrollbar luxury */
    ::-webkit-scrollbar { width: 6px; }
    ::-webkit-scrollbar-track { background: #cbd5e1; border-radius: 10px; }
    ::-webkit-scrollbar-thumb { background: linear-gradient(135deg, #38bdf8, #0ea5e9); border-radius: 10px; }
  </style>
</head>
<body>

  <!-- Animated gradient background layer -->
  <div class="animated-bg"></div>

  <!-- Glowing orbs -->
  <div class="glow-orb" style="width: 500px; height: 500px; background: #7dd3fc; top: -200px; left: -150px;"></div>
  <div class="glow-orb" style="width: 600px; height: 600px; background: #a5f3fc; bottom: -200px; right: -150px; animation-duration: 25s;"></div>
  <div class="glow-orb" style="width: 400px; height: 400px; background: #bae6fd; top: 40%; left: 70%; animation-duration: 18s;"></div>

  <!-- Premium droplets & sparkles containers -->
  <div id="premiumDrops" class="absolute inset-0 pointer-events-none z-0"></div>
  <div id="sparklesLayer" class="absolute inset-0 pointer-events-none z-0"></div>

  <!-- Mouse follower glow -->
  <div class="cursor-glow" id="cursorGlow"></div>

  <!-- HERO section - immersive -->
  <section class="relative min-h-screen flex items-center justify-start overflow-hidden">
    <div class="relative z-10 max-w-6xl mx-auto px-5 md:px-8 w-full py-16">
      <div class="inline-flex items-center gap-2 bg-white/50 backdrop-blur-md px-5 py-2 rounded-full text-sm text-sky-800 border border-white/60 shadow-lg mb-6 animate-pulse">
        <i class="fas fa-infinity text-sky-500"></i>
        <span class="font-semibold">AI‑powered time capsule ✨</span>
      </div>
      <h1 class="text-5xl md:text-7xl font-extrabold tracking-tight text-slate-800 drop-shadow-sm text-left">
        <span id="typewriterSpan" class="bg-gradient-to-r from-sky-700 via-cyan-600 to-blue-600 bg-clip-text text-transparent typewriter-glow"></span>
      </h1>
      <p class="text-lg md:text-xl text-slate-700 max-w-2xl mt-6 drop-shadow-sm text-left leading-relaxed backdrop-blur-sm bg-white/20 p-3 rounded-2xl inline-block">
        Relive every heartbeat, every twist, every triumph — your personal timeline, brought to life with serenity.
      </p>
      <div class="mt-10 flex flex-wrap gap-5 justify-start">
        <a href="/login" class="btn-ripple pulse-super bg-gradient-to-r from-sky-500 to-cyan-500 hover:from-sky-600 hover:to-cyan-600 text-white font-bold py-4 px-10 rounded-full text-lg transition-all duration-300 hover:scale-105 shadow-2xl flex items-center gap-3">
          <i class="fas fa-play-circle text-2xl"></i> Start Replaying Your Life
        </a>
        <a href="#timeline" class="bg-white/40 backdrop-blur-md border border-white/50 text-sky-800 font-semibold py-4 px-8 rounded-full hover:bg-white/70 transition-all duration-300 flex items-center gap-2 shadow-md hover:shadow-xl">
          <i class="fas fa-scroll"></i> Explore timeline
        </a>
      </div>
      <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce z-10">
        <i class="fas fa-chevron-down text-sky-500 text-2xl"></i>
      </div>
    </div>
  </section>

  <!-- TIMELINE PREVIEW SECTION - 3D interactive -->
  <section id="timeline" class="py-24 relative">
    <div class="max-w-6xl mx-auto px-6">
      <div class="text-center mb-16 reveal-zoom">
        <span class="text-sky-700 font-semibold tracking-wide bg-white/50 backdrop-blur-md px-4 py-1.5 rounded-full text-sm border border-sky-200 shadow-sm">
          <i class="fas fa-timeline"></i> your life chapters
        </span>
        <h2 class="text-4xl md:text-5xl font-bold text-slate-800 mt-4">Watch your story <span class="text-sky-500 animate-pulse">unfold</span></h2>
        <p class="text-slate-600 max-w-2xl mx-auto mt-3">Every moment is a frame — scroll to relive the journey.</p>
      </div>

      <div class="relative">
        <!-- Glowing timeline line -->
        <div class="absolute left-5 md:left-1/2 transform md:-translate-x-0.5 top-0 bottom-0 w-0.5 bg-gradient-to-b from-sky-300 via-cyan-400 to-sky-300 rounded-full shadow-lg"></div>

        <div class="space-y-12">
          <!-- Enhanced timeline cards with 3D tilt -->
          <div class="relative flex flex-col md:flex-row md:items-center gap-6 reveal-zoom">
            <div class="md:w-1/2 md:pr-12 md:text-right">
              <div class="glass-premium rounded-2xl p-6 timeline-card">
                <div class="inline-block px-3 py-1 rounded-full text-sky-700 text-sm font-bold bg-sky-100/80 shadow-sm">2024 · Spring</div>
                <h3 class="text-2xl font-bold mt-2 text-slate-800 flex items-center gap-2 justify-end"><span>🎓</span> New chapter · College begins</h3>
                <p class="text-slate-600 mt-2">First taste of independence, late‑night coding sessions, and friendships that felt like home.</p>
                <div class="mt-3 text-sky-500 text-xs flex gap-2 justify-end"><i class="fas fa-heart"></i> 24 memories</div>
              </div>
            </div>
            <div class="absolute left-2 md:left-1/2 transform -translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-br from-sky-400 to-cyan-400 border-4 border-white shadow-xl z-10 animate-pulse"></div>
            <div class="md:w-1/2 md:pl-12"></div>
          </div>

          <div class="relative flex flex-col md:flex-row md:items-center gap-6 reveal-zoom" style="transition-delay: 0.1s">
            <div class="md:w-1/2 md:pr-12"></div>
            <div class="absolute left-2 md:left-1/2 transform -translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-br from-indigo-400 to-sky-400 border-4 border-white shadow-xl z-10 animate-pulse"></div>
            <div class="md:w-1/2 md:pl-12">
              <div class="glass-premium rounded-2xl p-6 timeline-card">
                <div class="inline-block px-3 py-1 rounded-full text-indigo-700 text-sm font-bold bg-indigo-50/80">2024 · Summer</div>
                <h3 class="text-2xl font-bold mt-2 text-slate-800"><span>🤝</span> First real bond · True friendship</h3>
                <p class="text-slate-600 mt-2">Vulnerability, trust, and moments that reminded you that connection heals.</p>
                <div class="mt-3 text-indigo-400 text-xs flex gap-2"><i class="fas fa-hand-holding-heart"></i> 18 shared moments</div>
              </div>
            </div>
          </div>

          <div class="relative flex flex-col md:flex-row md:items-center gap-6 reveal-zoom" style="transition-delay: 0.2s">
            <div class="md:w-1/2 md:pr-12 md:text-right">
              <div class="glass-premium rounded-2xl p-6 timeline-card">
                <div class="inline-block px-3 py-1 rounded-full text-amber-600 text-sm font-bold bg-amber-50/80">2025 · Winter</div>
                <h3 class="text-2xl font-bold mt-2 text-slate-800"><span>⚡</span> Pressure period · Growth through fire</h3>
                <p class="text-slate-600 mt-2">Stressful deadlines and inner battles — but resilience was forged stronger.</p>
                <div class="mt-3 text-amber-500 text-xs flex gap-2 justify-end"><i class="fas fa-fire"></i> breakthrough energy</div>
              </div>
            </div>
            <div class="absolute left-2 md:left-1/2 transform -translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-br from-amber-400 to-orange-400 border-4 border-white shadow-xl z-10 animate-pulse"></div>
            <div class="md:w-1/2 md:pl-12"></div>
          </div>

          <div class="relative flex flex-col md:flex-row md:items-center gap-6 reveal-zoom" style="transition-delay: 0.3s">
            <div class="md:w-1/2 md:pr-12"></div>
            <div class="absolute left-2 md:left-1/2 transform -translate-x-1/2 w-10 h-10 rounded-full bg-gradient-to-br from-emerald-400 to-teal-400 border-4 border-white shadow-xl z-10 animate-pulse"></div>
            <div class="md:w-1/2 md:pl-12">
              <div class="glass-premium rounded-2xl p-6 timeline-card">
                <div class="inline-block px-3 py-1 rounded-full text-emerald-700 text-sm font-bold bg-emerald-50/80">2025 · Present</div>
                <h3 class="text-2xl font-bold mt-2 text-slate-800"><span>🌱</span> Breakthrough · Bloom phase</h3>
                <p class="text-slate-600 mt-2">Self‑discovery, purpose, and learning to enjoy the unfolding journey.</p>
                <div class="mt-3 text-emerald-500 text-xs flex gap-2"><i class="fas fa-leaf"></i> thriving now</div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <!-- AI CHAT - with glassmorphism and neat glow -->
  <section class="py-20 relative">
    <div class="max-w-3xl mx-auto px-6">
      <div class="text-center mb-10 reveal-zoom">
        <i class="fas fa-robot text-5xl text-sky-500 mb-3 animate-bounce"></i>
        <h2 class="text-3xl md:text-4xl font-bold text-slate-800">🧠 Meet your memory AI</h2>
        <p class="text-slate-600 mt-2">Ask anything — get insights from your imagined timeline.</p>
      </div>

      <div class="glass-premium rounded-2xl shadow-2xl overflow-hidden reveal-zoom">
        <div class="bg-white/50 backdrop-blur-lg px-5 py-3 border-b border-white/40 flex items-center gap-2">
          <i class="fas fa-brain text-sky-500 text-xl"></i>
          <span class="font-bold text-slate-700">Life Replay Assistant</span>
          <span class="text-xs bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full ml-auto shadow-sm">✨ emotional AI</span>
        </div>
        <div id="chatContainer" class="h-80 overflow-y-auto p-5 space-y-4 bg-white/30 custom-scroll"></div>
        <div class="border-t border-white/40 p-4 bg-white/40 flex gap-3">
          <input type="text" id="chatInput" placeholder="Try: 'Summarize my life' or 'How was 2025 for me?'" class="flex-1 rounded-xl bg-white/80 border border-sky-200 px-4 py-2 text-slate-700 placeholder:text-slate-400 focus:ring-2 focus:ring-sky-400 focus:border-transparent outline-none transition shadow-sm">
          <button id="sendChatBtn" class="bg-gradient-to-r from-sky-500 to-cyan-500 hover:scale-105 text-white px-5 rounded-xl transition-all flex items-center gap-2 shadow-lg">
            <i class="fas fa-paper-plane"></i> Send
          </button>
        </div>
        <div class="text-center text-xs text-slate-500 py-2 bg-white/20">
          <i class="fas fa-shield-alt"></i> Demo AI — reflects emotional storytelling
        </div>
      </div>
    </div>
  </section>

  <!-- FINAL CTA with particle burst -->
  <section class="py-20 text-center relative">
    <div class="max-w-4xl mx-auto px-6 reveal-zoom">
      <h2 class="text-4xl md:text-5xl font-bold text-slate-800">Ready to capture your journey?</h2>
      <p class="text-slate-600 text-lg mt-4 max-w-xl mx-auto">Start writing your legacy, track moods, and let AI guide your reflection.</p>
      <a href="#" class="inline-flex items-center gap-2 mt-8 px-10 py-4 bg-gradient-to-r from-sky-500 to-cyan-500 hover:from-sky-600 hover:to-cyan-600 text-white font-bold rounded-full shadow-2xl transition-all duration-300 text-lg pulse-super hover:scale-105">
        <i class="fas fa-play-circle"></i> Begin your Life Replay
      </a>
    </div>
  </section>

  <footer class="border-t border-sky-200/50 py-8 text-center text-slate-500 text-sm bg-white/30 backdrop-blur-sm">
    <p>© 2025 Life Replay System — Rewind. Reflect. Relive. ✨</p>
  </footer>

  <script>
    // Generate premium droplets (more organic)
    const dropsContainer = document.getElementById('premiumDrops');
    for (let i = 0; i < 60; i++) {
      let d = document.createElement('div');
      d.classList.add('droplet-premium');
      let size = Math.random() * 60 + 15;
      d.style.width = size + 'px';
      d.style.height = size * 0.9 + 'px';
      d.style.left = Math.random() * 100 + '%';
      d.style.top = Math.random() * 100 + '%';
      d.style.animationDuration = 10 + Math.random() * 18 + 's';
      d.style.animationDelay = Math.random() * 15 + 's';
      dropsContainer.appendChild(d);
    }

    // Generate sparkling stars
    const sparkleLayer = document.getElementById('sparklesLayer');
    for (let i = 0; i < 150; i++) {
      let s = document.createElement('div');
      s.classList.add('sparkle');
      let sz = Math.random() * 4 + 1;
      s.style.width = sz + 'px';
      s.style.height = sz + 'px';
      s.style.left = Math.random() * 100 + '%';
      s.style.top = Math.random() * 100 + '%';
      s.style.animationDuration = 2 + Math.random() * 4 + 's';
      s.style.animationDelay = Math.random() * 5 + 's';
      s.style.backgroundColor = `rgba(255, 255, 255, ${0.5 + Math.random() * 0.5})`;
      sparkleLayer.appendChild(s);
    }

    // Cursor glow effect
    const cursorGlow = document.getElementById('cursorGlow');
    document.addEventListener('mousemove', (e) => {
      cursorGlow.style.transform = `translate(${e.clientX}px, ${e.clientY}px)`;
    });
    document.addEventListener('mouseleave', () => {
      cursorGlow.style.opacity = '0';
    });
    document.addEventListener('mouseenter', () => {
      cursorGlow.style.opacity = '1';
    });

    // Typewriter effect
    const fullText = "Your life is not forgotten. It's replayed.";
    let idx = 0;
    const typeSpan = document.getElementById('typewriterSpan');
    typeSpan.innerHTML = '';
    function typeNextLetter() {
      if (idx < fullText.length) {
        typeSpan.innerHTML += fullText.charAt(idx);
        idx++;
        setTimeout(typeNextLetter, 65);
      } else {
        typeSpan.style.borderRight = 'none';
      }
    }
    typeNextLetter();

    // Scroll reveal zoom
    const revealObs = new IntersectionObserver((entries) => {
      entries.forEach(e => e.isIntersecting && e.target.classList.add('active'));
    }, { threshold: 0.2 });
    document.querySelectorAll('.reveal-zoom').forEach(el => revealObs.observe(el));

    // CHAT LOGIC upgraded
    const chatContainer = document.getElementById('chatContainer');
    function appendMessage(sender, text) {
      const msgDiv = document.createElement('div');
      msgDiv.className = `chat-message flex justify-start`;
      msgDiv.innerHTML = `
        <div class="max-w-[85%] ${sender === 'user' ? 'bg-gradient-to-r from-sky-500 to-cyan-500 text-white rounded-br-none shadow-md' : 'glass-premium text-slate-700 rounded-bl-none'} rounded-2xl px-4 py-2 backdrop-blur-sm">
          <div class="text-xs opacity-90 mb-0.5 font-semibold">${sender === 'user' ? 'You' : '🧠 AI · Life Replay'}</div>
          <p class="text-sm">${text}</p>
        </div>
      `;
      chatContainer.appendChild(msgDiv);
      chatContainer.scrollTop = chatContainer.scrollHeight;
    }

    function simulateAiTyping(response) {
      const typingDiv = document.createElement('div');
      typingDiv.className = 'flex justify-start chat-message';
      typingDiv.innerHTML = `<div class="glass-premium rounded-2xl px-4 py-2"><span class="text-sm text-slate-600">AI <span class="typing-dots"></span></span></div>`;
      chatContainer.appendChild(typingDiv);
      chatContainer.scrollTop = chatContainer.scrollHeight;
      setTimeout(() => {
        typingDiv.remove();
        appendMessage('ai', response);
      }, 1300);
    }

    function handleUserMessage(msg) {
      appendMessage('user', msg);
      const lower = msg.toLowerCase();
      if (lower.includes('summarize my life')) {
        simulateAiTyping("✨ Your life replay shows a beautiful arc: 2024 was about new beginnings and deep bonds. 2025 brought storms that turned into strength. Right now you're blooming with purpose. Keep writing your legacy.");
      } else if (lower.includes('how was 2025')) {
        simulateAiTyping("📆 2025 was a year of transformation. The winter challenged you, but by spring you found resilience. Your timeline glows with growth — from pressure to breakthrough.");
      } else if (lower.includes('happiness') || lower.includes('mood')) {
        simulateAiTyping("😊 Your happiest moments cluster around creative nights and heart-to-heart talks. Your mood elevation peaks on weekends — those are your golden hours.");
      } else if (lower.includes('grateful')) {
        simulateAiTyping("🙏 Gratitude flows strongest during autumn memories and friend reunions. You've recorded 32 thankful moments — a heart full of warmth.");
      } else {
        simulateAiTyping("💫 I'm your memory companion. Try 'Summarize my life', 'How was 2025?', or 'What makes me happy?' I'll reveal emotional insights from your journey.");
      }
    }

    document.getElementById('sendChatBtn').addEventListener('click', () => {
      const input = document.getElementById('chatInput');
      const txt = input.value.trim();
      if (txt) handleUserMessage(txt);
      input.value = '';
    });
    document.getElementById('chatInput').addEventListener('keypress', (e) => e.key === 'Enter' && document.getElementById('sendChatBtn').click());

    setTimeout(() => {
      appendMessage('ai', "👋 Hi, I'm your Life Replay AI. Ask me to 'Summarize my life' or explore your emotional timeline. Every moment matters.");
    }, 500);

    // additional 3D tilt on timeline cards (intensified)
    const cards = document.querySelectorAll('.timeline-card');
    cards.forEach(card => {
      card.addEventListener('mousemove', (e) => {
        const rect = card.getBoundingClientRect();
        const x = (e.clientX - rect.left) / rect.width - 0.5;
        const y = (e.clientY - rect.top) / rect.height - 0.5;
        card.style.transform = `perspective(1200px) rotateY(${x * 8}deg) rotateX(${y * -5}deg) translateY(-6px)`;
      });
      card.addEventListener('mouseleave', () => {
        card.style.transform = '';
      });
    });
  </script>
</body>
</html>
