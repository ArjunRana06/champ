<header class="fixed top-0 left-0 w-full z-50 transition-all duration-500"
        :class="scrollY > 50 ? 'bg-forest/90 backdrop-blur-xl shadow-2xl' : 'bg-transparent'">
    <div class="container mx-auto px-6 py-4 flex items-center justify-between">
        <a href="/" class="flex items-center space-x-3 group">
            <i class="fas fa-mountain text-gold text-3xl group-hover:animate-float"></i>
            <span class="font-display text-2xl font-black tracking-tight text-white drop-shadow-lg"
                  x-text="lang === 'en' ? 'Trip Planner Nepal' : 'यात्रा योजनाकार नेपाल'"></span>
        </a>
        <div class="hidden md:flex items-center space-x-6">
            <a href="/login"
               class="relative overflow-hidden bg-gold text-forest px-6 py-2.5 rounded-full font-bold shadow-lg hover:shadow-xl transition-all group">
                <span class="relative z-10" x-text="lang === 'en' ? 'Login / Register' : 'लगइन / दर्ता'"></span>
                <div class="absolute inset-0 bg-white scale-x-0 group-hover:scale-x-100 transition-transform origin-left duration-300"></div>
            </a>
            <div class="flex items-center space-x-3 text-white/90">
                <span @click="toggleLang()" class="cursor-pointer transition hover:scale-110"
                      :class="lang === 'en' ? 'text-gold font-bold' : 'hover:text-gold'">EN</span>
                <span>|</span>
                <span @click="toggleLang()" class="cursor-pointer transition hover:scale-110"
                      :class="lang === 'ne' ? 'text-gold font-bold' : 'hover:text-gold'">नेपाली</span>
            </div>
        </div>
        <button @click="mobileMenuOpen = !mobileMenuOpen" class="md:hidden text-white text-2xl focus:outline-none">
            <i class="fas" :class="mobileMenuOpen ? 'fa-times' : 'fa-bars'"></i>
        </button>
    </div>
</header>
