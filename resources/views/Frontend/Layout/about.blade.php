@extends('layouts.master')

@section('content')
<!-- Hero -->
<section class="relative py-32 bg-gradient-to-r from-forest to-forest/80 text-white overflow-hidden">
    <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1920&auto=format')] bg-cover bg-center opacity-20"></div>
    <div class="container mx-auto px-6 text-center relative z-10">
        <h1 class="text-5xl md:text-7xl font-display font-extrabold mb-6" x-text="lang === 'en' ? 'About Us' : 'हाम्रो बारेमा'"></h1>
        <div class="w-24 h-1 bg-gold mx-auto rounded-full"></div>
        <p class="text-xl max-w-3xl mx-auto mt-6" x-text="lang === 'en' ? 'We\'re on a mission to revolutionize travel planning in Nepal using the power of AI.' : 'हामी एआईको शक्ति प्रयोग गरी नेपालमा यात्रा योजनालाई क्रान्तिकारी बनाउने अभियानमा छौं।'"></p>
    </div>
</section>

<!-- Story -->
<section class="py-28 bg-cream">
    <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12 items-center">
            <div class="reveal">
                <h2 class="text-4xl md:text-5xl font-display font-extrabold text-forest" x-text="lang === 'en' ? 'Our Story' : 'हाम्रो कथा'"></h2>
                <div class="w-20 h-1 bg-gold my-4"></div>
                <p class="text-gray-700 text-lg leading-relaxed" x-text="lang === 'en' ? 'Founded in 2023, Trip Planner Nepal was born from a passion for travel and a love for Nepal\'s incredible diversity. We saw that travelers often struggled to find authentic experiences and efficient itineraries. So we built an AI-powered platform that understands your preferences and crafts the perfect journey—blending cultural immersion, adventure, and relaxation.' : 'सन् २०२३ मा स्थापित, यात्रा योजनाकार नेपाल यात्राप्रतिको जोश र नेपालको अद्भुत विविधताप्रतिको मायाबाट जन्मिएको हो। हामीले देख्यौं कि यात्रीहरू प्रायः प्रामाणिक अनुभवहरू र कुशल यात्रा योजनाहरू खोज्न संघर्ष गर्छन्। त्यसैले हामीले एउटा एआई-संचालित प्लेटफर्म बनायौं जसले तपाईंको प्राथमिकताहरू बुझ्दछ र उत्तम यात्रा तयार गर्दछ — सांस्कृतिक अनुभव, साहसिक, र विश्रामको मिश्रण।'"></p>
            </div>
            <div class="reveal">
                <img src="https://images.unsplash.com/photo-1526392062735-9b0125b1b3b0?w=800&auto=format" alt="Story" class="rounded-3xl shadow-xl">
            </div>
        </div>
    </div>
</section>

<!-- Mission & Vision -->
<section class="py-28 bg-white">
    <div class="container mx-auto px-6">
        <div class="grid md:grid-cols-2 gap-12">
            <div class="text-center p-8 rounded-3xl glass-dark border border-gold/20 hover:shadow-2xl transition reveal">
                <i class="fas fa-bullseye text-5xl text-gold mb-4"></i>
                <h3 class="text-3xl font-display font-bold text-forest" x-text="lang === 'en' ? 'Our Mission' : 'हाम्रो लक्ष्य'"></h3>
                <p class="text-gray-700 mt-4 leading-relaxed" x-text="lang === 'en' ? 'To empower travelers with personalized, AI-driven itineraries that unlock the true essence of Nepal.' : 'यात्रीहरूलाई व्यक्तिगत, एआई-संचालित यात्रा योजनाहरू प्रदान गर्नु जसले नेपालको वास्तविक सार उजागर गर्दछ।'"></p>
            </div>
            <div class="text-center p-8 rounded-3xl glass-dark border border-gold/20 hover:shadow-2xl transition reveal">
                <i class="fas fa-eye text-5xl text-gold mb-4"></i>
                <h3 class="text-3xl font-display font-bold text-forest" x-text="lang === 'en' ? 'Our Vision' : 'हाम्रो दृष्टिकोण'"></h3>
                <p class="text-gray-700 mt-4 leading-relaxed" x-text="lang === 'en' ? 'Become the go-to travel companion for every Nepal explorer, leveraging AI to make travel seamless and memorable.' : 'हरेक नेपाल भ्रमणकारीको लागि मुख्य यात्रा साथी बन्न, एआईको उपयोग गरी यात्रालाई सहज र यादगार बनाउन।'"></p>
            </div>
        </div>
    </div>
</section>

<!-- Team -->
<section class="py-28 bg-cream">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16 reveal">
            <h2 class="text-4xl md:text-5xl font-display font-extrabold text-forest" x-text="lang === 'en' ? 'Meet the Team' : 'टोलीलाई चिन्नुहोस्'"></h2>
            <div class="w-24 h-1 bg-gold mx-auto mt-4 rounded-full"></div>
            <p class="text-gray-600 max-w-2xl mx-auto mt-4" x-text="lang === 'en' ? 'Passionate locals and travel experts dedicated to your journey.' : 'तपाईंको यात्राको लागि समर्पित जोशीला स्थानीय र यात्रा विशेषज्ञहरू।'"></p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
            @php
                $team = [
                    ['name' => 'Rajesh Thapa', 'role_en' => 'Co-Founder & CEO', 'role_ne' => 'सह-संस्थापक र प्रमुख कार्यकारी अधिकृत', 'avatar' => 'https://randomuser.me/api/portraits/men/32.jpg', 'bio_en' => 'Avid trekker and tech enthusiast.', 'bio_ne' => 'उत्सुक ट्रेकर र प्रविधि उत्साही।'],
                    ['name' => 'Sita Adhikari', 'role_en' => 'Head of Product', 'role_ne' => 'उत्पादन प्रमुख', 'avatar' => 'https://randomuser.me/api/portraits/women/68.jpg', 'bio_en' => 'Designs experiences with a human touch.', 'bio_ne' => 'मानवीय स्पर्शका साथ अनुभवहरू डिजाइन गर्छिन्।'],
                    ['name' => 'Anil Gurung', 'role_en' => 'AI Engineer', 'role_ne' => 'एआई ईन्जिनियर', 'avatar' => 'https://randomuser.me/api/portraits/men/91.jpg', 'bio_en' => 'Makes the magic happen behind the scenes.', 'bio_ne' => 'पर्दा पछाडि जादू लागू गर्दछ।'],
                ];
            @endphp
            @foreach($team as $member)
            <div class="bg-white rounded-3xl shadow-xl overflow-hidden tilt-card reveal">
                <img src="{{ $member['avatar'] }}" alt="{{ $member['name'] }}" class="w-full h-64 object-cover">
                <div class="p-6 text-center">
                    <h3 class="text-2xl font-bold text-forest">{{ $member['name'] }}</h3>
                    <p class="text-gold font-semibold" x-text="lang === 'en' ? '{{ $member['role_en'] }}' : '{{ $member['role_ne'] }}'"></p>
                    <p class="text-gray-600 mt-2" x-text="lang === 'en' ? '{{ $member['bio_en'] }}' : '{{ $member['bio_ne'] }}'"></p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

<!-- Stats -->
<section class="py-20 bg-gradient-to-r from-forest to-forest/80 text-white">
    <div class="container mx-auto px-6">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8 text-center">
            <div>
                <div class="text-5xl font-bold text-gold">10K+</div>
                <p class="mt-2 text-lg" x-text="lang === 'en' ? 'Happy Travelers' : 'सन्तुष्ट यात्री'"></p>
            </div>
            <div>
                <div class="text-5xl font-bold text-gold">50+</div>
                <p class="mt-2 text-lg" x-text="lang === 'en' ? 'Destinations' : 'गन्तव्यहरू'"></p>
            </div>
            <div>
                <div class="text-5xl font-bold text-gold">200+</div>
                <p class="mt-2 text-lg" x-text="lang === 'en' ? 'Custom Itineraries' : 'अनुकूल यात्रा योजनाहरू'"></p>
            </div>
            <div>
                <div class="text-5xl font-bold text-gold">24/7</div>
                <p class="mt-2 text-lg" x-text="lang === 'en' ? 'AI Support' : 'एआई सहायता'"></p>
            </div>
        </div>
    </div>
</section>
@endsection
