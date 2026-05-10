<footer class="bg-forest text-white pt-20 pb-8 relative overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/black-scales.png')] opacity-5"></div>
    <div class="container mx-auto px-6 relative z-10">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-12">
            <div>
                <div class="flex items-center space-x-2 mb-4">
                    <i class="fas fa-mountain text-gold text-3xl"></i>
                    <h4 class="font-display text-2xl font-bold" x-text="lang === 'en' ? 'Trip Planner Nepal' : 'यात्रा योजनाकार नेपाल'"></h4>
                </div>
                <p class="text-sm opacity-80 leading-relaxed" x-text="lang === 'en' ? 'AI-powered travel companion for authentic Nepalese experiences.' : 'प्रामाणिक नेपाली अनुभवहरूको लागि एआई-संचालित यात्रा साथी।'"></p>
            </div>
            <div>
                <h5 class="font-bold text-gold text-lg mb-4" x-text="lang === 'en' ? 'Quick links' : 'द्रुत लिङ्कहरू'"></h5>
                <ul class="space-y-2 text-sm">
                    <li><a href="#" class="hover:text-gold transition" x-text="lang === 'en' ? 'Home' : 'गृहपृष्ठ'"></a></li>
                    <li><a href="#" class="hover:text-gold transition" x-text="lang === 'en' ? 'Destinations' : 'गन्तव्यहरू'"></a></li>
                    <li><a href="#" class="hover:text-gold transition" x-text="lang === 'en' ? 'Blog' : 'ब्लग'"></a></li>
                    <li><a href="#" class="hover:text-gold transition" x-text="lang === 'en' ? 'About' : 'हाम्रो बारेमा'"></a></li>
                </ul>
            </div>
            <div>
                <h5 class="font-bold text-gold text-lg mb-4" x-text="lang === 'en' ? 'Contact' : 'सम्पर्क'"></h5>
                <ul class="space-y-2 text-sm">
                    <li><i class="fas fa-envelope mr-2 text-gold"></i> hello@tripplanner.np</li>
                    <li><i class="fas fa-phone mr-2 text-gold"></i> +977 1 2345678</li>
                    <li><i class="fas fa-map-marker-alt mr-2 text-gold"></i> Kathmandu, Nepal</li>
                </ul>
            </div>
            <div>
                <h5 class="font-bold text-gold text-lg mb-4" x-text="lang === 'en' ? 'Subscribe' : 'सदस्यता लिनुहोस्'"></h5>
                <p class="text-sm mb-3" x-text="lang === 'en' ? 'Get travel tips & AI updates' : 'यात्रा सुझाव र एआई अपडेटहरू प्राप्त गर्नुहोस्'"></p>
                <form class="flex flex-col gap-2">
                    <input type="email" placeholder="Your email" class="px-4 py-2 rounded-full text-gray-800 bg-white/90 focus:outline-none focus:ring-2 focus:ring-gold">
                    <button class="bg-gold text-forest py-2 rounded-full font-semibold hover:bg-amber-400 transition shadow-md" x-text="lang === 'en' ? 'Subscribe' : 'सदस्यता लिनुहोस्'"></button>
                </form>
                <div class="flex space-x-5 mt-6 text-2xl">
                    <a href="#" class="hover:text-gold transition"><i class="fab fa-facebook"></i></a>
                    <a href="#" class="hover:text-gold transition"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="hover:text-gold transition"><i class="fab fa-youtube"></i></a>
                </div>
            </div>
        </div>
        <div class="text-center text-sm border-t border-white/20 mt-12 pt-6">
            © {{ date('Y') }} Trip Planner Nepal — AI journey designer
        </div>
    </div>
</footer>
