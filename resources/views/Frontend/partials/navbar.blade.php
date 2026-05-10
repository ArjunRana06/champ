<nav class="hidden md:block fixed top-20 left-0 w-full z-40 bg-white/50 backdrop-blur-md border-b border-gold/20">
    <div class="container mx-auto px-6">
        <ul class="flex justify-center space-x-12 py-3 text-sm font-semibold">
            <li><a href="#" class="hover:text-gold transition-colors"
                    x-text="lang === 'en' ? 'Home' : 'गृहपृष्ठ'"></a></li>
            <li><a href="#" class="hover:text-gold transition-colors"
                    x-text="lang === 'en' ? 'Destinations' : 'गन्तव्यहरू'"></a></li>
            <li><a href="#" class="text-gold border-b-2 border-gold pb-1"
                    x-text="lang === 'en' ? 'Plan Your Trip' : 'यात्रा योजना बनाउनुहोस्'"></a></li>
            <li><a href="#" class="hover:text-gold transition-colors"
                    x-text="lang === 'en' ? 'About' : 'हाम्रो बारेमा'"></a></li>
            <li><a href="#" class="hover:text-gold transition-colors"
                    x-text="lang === 'en' ? 'Contact' : 'सम्पर्क'"></a></li>
        </ul>
    </div>
</nav>

<div x-show="mobileMenuOpen" x-cloak class="fixed inset-0 bg-black/70 z-50 md:hidden" @click="mobileMenuOpen = false">
</div>
<div x-show="mobileMenuOpen" x-transition:enter="transition ease-out duration-300"
    x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
    x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0"
    x-transition:leave-end="-translate-x-full"
    class="fixed top-0 left-0 w-4/5 max-w-sm h-full bg-white z-50 shadow-2xl p-6 md:hidden">
    <div class="flex justify-between items-center border-b pb-4">
        <span class="font-display text-xl font-bold text-forest" x-text="lang === 'en' ? 'Menu' : 'मेनु'"></span>
        <button @click="mobileMenuOpen = false" class="text-gray-500 text-2xl">&times;</button>
    </div>
    <ul class="mt-6 space-y-5">
        <li><a href="{{ route('home') }}" class="hover:text-gold transition-colors"
                x-text="lang === 'en' ? 'Home' : 'गृहपृष्ठ'"></a></li>
        <li><a href="{{ route('destination') }}" class="hover:text-gold transition-colors"
                x-text="lang === 'en' ? 'Destinations' : 'गन्तव्यहरू'"></a></li>
        <li><a href="{{ route('plan') }}" class="text-gold border-b-2 border-gold pb-1"
                x-text="lang === 'en' ? 'Plan Your Trip' : 'यात्रा योजना बनाउनुहोस्'"></a></li>
        <li><a href="{{ route('about') }}" class="hover:text-gold transition-colors"
                x-text="lang === 'en' ? 'About' : 'हाम्रो बारेमा'"></a></li>
        <li><a href="{{ route('contact') }}" class="hover:text-gold transition-colors"
                x-text="lang === 'en' ? 'Contact' : 'सम्पर्क'"></a></li>
        <li class="pt-4"><a href="/login"
                class="inline-block bg-gold text-forest px-6 py-2 rounded-full font-semibold"
                x-text="lang === 'en' ? 'Login / Register' : 'लगइन / दर्ता'"></a></li>
        <li class="pt-2 flex space-x-4 text-sm">
            <span @click="toggleLang()" class="cursor-pointer"
                :class="lang === 'en' ? 'text-gold font-bold' : 'hover:text-gold'">EN</span>
            <span>|</span>
            <span @click="toggleLang()" class="cursor-pointer"
                :class="lang === 'ne' ? 'text-gold font-bold' : 'hover:text-gold'">नेपाली</span>
        </li>
    </ul>
</div>
