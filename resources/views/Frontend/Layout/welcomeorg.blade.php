@extends('Frontend.Layout.master')

@section('content')
    <!-- Hero Section with Three.js canvas -->
    <section class="relative h-screen w-full overflow-hidden">
        <canvas id="hero-canvas" class="absolute top-0 left-0 w-full h-full"></canvas>
        <div class="absolute inset-0 bg-gradient-to-b from-forest/70 via-forest/50 to-forest/80"></div>
        <div class="relative z-10 flex flex-col items-center justify-center h-full text-center text-white px-4">
            <h1 class="text-5xl md:text-7xl font-display font-black mb-6 leading-tight shimmer animate-glow"
                x-text="lang === 'en' ? 'Plan Your Perfect Trip to Nepal with AI' : 'एआईसँग नेपालको उत्तम यात्रा योजना बनाउनुहोस्'"></h1>
            <p class="text-xl md:text-2xl max-w-3xl mb-10 font-light drop-shadow-lg"
               x-text="lang === 'en' ? 'Discover hidden gems, trekking routes, cultural spots, and food experiences tailored just for you.' : 'तपाईंको लागि मात्रै तयार पारिएका गोप्य रत्नहरू, ट्रेकिङ मार्गहरू, सांस्कृतिक स्थलहरू, र खाद्य अनुभवहरू पत्ता लगाउनुहोस्।'"></p>
            <div class="flex flex-col sm:flex-row gap-6">
                <a href="#" class="bg-gold text-forest px-10 py-4 rounded-full text-lg font-bold shadow-2xl hover:scale-105 transition-all flex items-center gap-2">
                    <i class="fas fa-route"></i> <span x-text="lang === 'en' ? 'Plan My Trip' : 'मेरो यात्रा योजना'"></span>
                </a>
                <a href="#" class="backdrop-blur-sm border-2 border-gold text-white px-10 py-4 rounded-full text-lg font-semibold hover:bg-gold hover:text-forest transition-all flex items-center gap-2">
                    <i class="fas fa-compass"></i> <span x-text="lang === 'en' ? 'Explore Destinations' : 'गन्तव्यहरू अन्वेषण गर्नुहोस्'"></span>
                </a>
            </div>
        </div>
        <div class="absolute bottom-8 left-1/2 transform -translate-x-1/2 animate-bounce">
            <i class="fas fa-chevron-down text-white text-2xl"></i>
        </div>
    </section>

    <!-- Destination Highlights -->
    <section class="py-28 bg-cream relative">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16 reveal">
                <span class="text-gold font-semibold tracking-wider uppercase text-sm"><i class="fas fa-map-marked-alt mr-1"></i> <span x-text="lang === 'en' ? 'Must-visit' : 'अवश्य भ्रमण गर्नुपर्ने'"></span></span>
                <h2 class="text-4xl md:text-6xl font-display font-extrabold text-forest mt-2" x-text="lang === 'en' ? 'Explore top destinations' : 'शीर्ष गन्तव्यहरू अन्वेषण गर्नुहोस्'"></h2>
                <div class="w-24 h-1 bg-gold mx-auto mt-4 rounded-full"></div>
                <p class="text-gray-600 max-w-2xl mx-auto mt-5 text-lg" x-text="lang === 'en' ? 'From ancient temples to breathtaking mountains — discover Nepal\'s wonders' : 'प्राचीन मन्दिरदेखि मनमोहक हिमालसम्म — नेपालका चमत्कारहरू पत्ता लगाउनुहोस्'"></p>
            </div>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
                @php
                    $destinations = [
                        ['name_en' => 'Kathmandu', 'name_ne' => 'काठमाडौं', 'img' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&auto=format', 'type_en' => 'Culture', 'type_ne' => 'संस्कृति', 'desc_en' => 'Temples, stupas, and vibrant streets — the cultural heart of Nepal.', 'desc_ne' => 'मन्दिरहरू, स्तूपहरू, र जीवन्त सडकहरू — नेपालको सांस्कृतिक केन्द्र।', 'overlay_en' => 'UNESCO sites · Durbar Square · Swayambhunath', 'overlay_ne' => 'युनेस्को स्थलहरू · दरबार स्क्वायर · स्वयम्भूनाथ', 'icon' => 'fa-map-pin'],
                        ['name_en' => 'Pokhara', 'name_ne' => 'पोखरा', 'img' => 'https://images.unsplash.com/photo-1526392062735-9b0125b1b3b0?w=800&auto=format', 'type_en' => 'Nature', 'type_ne' => 'प्रकृति', 'desc_en' => 'Lakes, paragliding, and panoramic views of the Annapurna range.', 'desc_ne' => 'तालहरू, प्याराग्लाइडिङ, र अन्नपूर्ण श्रृंखलाको मनोरम दृश्यहरू।', 'overlay_en' => 'Phewa Lake · World Peace Pagoda · Sarangkot', 'overlay_ne' => 'फेवा ताल · विश्व शान्ति स्तूप · साराङकोट', 'icon' => 'fa-parachute-box'],
                        ['name_en' => 'Chitwan', 'name_ne' => 'चितवन', 'img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&auto=format', 'type_en' => 'Wildlife', 'type_ne' => 'वन्यजन्तु', 'desc_en' => 'Jungle safaris, rhinos, and elephants in lush national park.', 'desc_ne' => 'हरियाली राष्ट्रिय निकुञ्जमा जंगल सफारी, गैंडा, र हात्तीहरू।', 'overlay_en' => 'Jungle walk · Canoeing · Bird watching', 'overlay_ne' => 'जंगल पदयात्रा · डुङ्गा चलाउने · चरा अवलोकन', 'icon' => 'fa-paw'],
                        ['name_en' => 'Lumbini', 'name_ne' => 'लुम्बिनी', 'img' => 'https://images.unsplash.com/photo-1580043621114-6721db12b9a1?w=800&auto=format', 'type_en' => 'Pilgrimage', 'type_ne' => 'तीर्थस्थल', 'desc_en' => 'Birthplace of Buddha, sacred gardens and monasteries.', 'desc_ne' => 'बुद्धको जन्मस्थल, पवित्र बगैंचा र मठहरू।', 'overlay_en' => 'Maya Devi Temple · World Peace Stupa', 'overlay_ne' => 'माया देवी मन्दिर · विश्व शान्ति स्तूप', 'icon' => 'fa-dharmachakra'],
                        ['name_en' => 'Annapurna Base Camp', 'name_ne' => 'अन्नपूर्ण आधार शिविर', 'img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&auto=format', 'type_en' => 'Adventure', 'type_ne' => 'साहसिक', 'desc_en' => 'Classic trek with stunning mountain vistas.', 'desc_ne' => 'अद्भुत हिमाली दृश्यहरू सहितको क्लासिक ट्रेक।', 'overlay_en' => 'Best season: Oct-Nov · tea houses · views', 'overlay_ne' => 'उत्तम मौसम: अक्टोबर-नोभेम्बर · चिया घरहरू · दृश्यहरू', 'icon' => 'fa-hiking'],
                        ['name_en' => 'Everest Base Camp', 'name_ne' => 'सगरमाथा आधार शिविर', 'img' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&auto=format', 'type_en' => 'Trek', 'type_ne' => 'ट्रेक', 'desc_en' => 'Walk in the footsteps of legends, up close with the world\'s top.', 'desc_ne' => 'पौराणिक व्यक्तित्वहरूको पाइला पछ्याउँदै, विश्वको शीर्षसँग नजिक।', 'overlay_en' => '12-14 days · Lukla flight · Khumbu glacier', 'overlay_ne' => '१२-१४ दिन · लुक्ला उडान · खुम्बु हिमनदी', 'icon' => 'fa-mountain'],
                    ];
                @endphp
                @foreach($destinations as $d)
                <div class="group relative rounded-3xl overflow-hidden shadow-2xl tilt-card transition-all duration-300 reveal">
                    <div class="relative overflow-hidden h-80">
                        <img src="{{ $d['img'] }}" alt="{{ $d['name_en'] }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-110">
                        <div class="absolute inset-0 bg-gradient-to-t from-forest/90 via-transparent to-transparent"></div>
                    </div>
                    <div class="absolute bottom-0 left-0 right-0 p-6 text-white">
                        <div class="flex justify-between items-center mb-2">
                            <h3 class="text-2xl font-bold" x-text="lang === 'en' ? '{{ $d['name_en'] }}' : '{{ $d['name_ne'] }}'"></h3>
                            <span class="bg-gold/90 text-forest text-xs px-3 py-1 rounded-full font-bold" x-text="lang === 'en' ? '{{ $d['type_en'] }}' : '{{ $d['type_ne'] }}'"></span>
                        </div>
                        <p class="text-sm opacity-90" x-text="lang === 'en' ? '{{ $d['desc_en'] }}' : '{{ $d['desc_ne'] }}'"></p>
                        <div class="mt-4 opacity-0 group-hover:opacity-100 transition-all duration-300">
                            <p class="text-xs"><i class="fas {{ $d['icon'] }} text-gold mr-1"></i> <span x-text="lang === 'en' ? '{{ $d['overlay_en'] }}' : '{{ $d['overlay_ne'] }}'"></span></p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="py-28 bg-gradient-to-br from-forest to-forest/80 text-white relative overflow-hidden">
        <div class="absolute top-0 left-0 w-full h-full bg-[url('https://www.transparenttextures.com/patterns/black-paper.png')] opacity-5"></div>
        <div class="container mx-auto px-6 relative z-10">
            <div class="text-center mb-16 reveal">
                <h2 class="text-4xl md:text-6xl font-display font-extrabold" x-text="lang === 'en' ? 'How it works' : 'यसरी काम गर्दछ'"></h2>
                <div class="w-24 h-1 bg-gold mx-auto mt-4 rounded-full"></div>
                <p class="text-gray-300 max-w-2xl mx-auto mt-5" x-text="lang === 'en' ? 'Your journey to Nepal starts in four intelligent steps' : 'नेपालको तपाईंको यात्रा चार बुद्धिमानी चरणहरूमा सुरु हुन्छ'"></p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-4 gap-8">
                @php
                    $steps = [
                        ['icon' => 'fa-clipboard-list', 'title_en' => 'Choose preferences', 'title_ne' => 'प्राथमिकताहरू छान्नुहोस्', 'desc_en' => 'Tell us your trip style, duration, and interests.', 'desc_ne' => 'हामीलाई तपाईंको यात्रा शैली, अवधि, र रुचिहरू बताउनुहोस्।'],
                        ['icon' => 'fa-robot', 'title_en' => 'AI generates itinerary', 'title_ne' => 'एआईले यात्रा योजना बनाउँदछ', 'desc_en' => 'Get a personalized plan with hidden gems and cultural tips.', 'desc_ne' => 'गोप्य रत्नहरू र सांस्कृतिक सुझावहरू सहित व्यक्तिगत योजना प्राप्त गर्नुहोस्।'],
                        ['icon' => 'fa-map-marked-alt', 'title_en' => 'Explore maps & tips', 'title_ne' => 'नक्सा र सुझावहरू अन्वेषण गर्नुहोस्', 'desc_en' => 'Interactive map, local food suggestions, and best times.', 'desc_ne' => 'अन्तरक्रियात्मक नक्सा, स्थानीय खाना सुझावहरू, र उत्तम समयहरू।'],
                        ['icon' => 'fa-share-alt', 'title_en' => 'Save & share', 'title_ne' => 'सुरक्षित गर्नुहोस् र साझा गर्नुहोस्', 'desc_en' => 'Keep your trip or share with travel buddies.', 'desc_ne' => 'तपाईंको यात्रा सुरक्षित गर्नुहोस् वा यात्रा साथीहरूसँग साझा गर्नुहोस्।'],
                    ];
                @endphp
                @foreach($steps as $idx => $step)
                <div class="text-center p-8 rounded-3xl glass-dark backdrop-blur-sm border border-white/20 hover:scale-105 transition-all duration-300 reveal">
                    <div class="w-20 h-20 bg-gold/20 rounded-full flex items-center justify-center text-3xl text-gold mx-auto mb-5">
                        <i class="fas {{ $step['icon'] }}"></i>
                    </div>
                    <h3 class="text-xl font-bold">{{ $idx+1 }}. <span x-text="lang === 'en' ? '{{ $step['title_en'] }}' : '{{ $step['title_ne'] }}'"></span></h3>
                    <p class="text-gray-300 mt-2" x-text="lang === 'en' ? '{{ $step['desc_en'] }}' : '{{ $step['desc_ne'] }}'"></p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Testimonials Marquee -->
    <section class="py-28 bg-cream overflow-hidden">
        <div class="container mx-auto px-6">
            <div class="text-center mb-12 reveal">
                <i class="fas fa-quote-left text-5xl text-gold/30"></i>
                <h2 class="text-4xl md:text-6xl font-display font-extrabold text-forest mt-4" x-text="lang === 'en' ? 'Traveler experiences' : 'यात्रीहरूको अनुभव'"></h2>
                <p class="text-gray-600 max-w-2xl mx-auto mt-4" x-text="lang === 'en' ? 'Real stories from people who planned their Nepal trip with us' : 'हामीसँग नेपाल यात्रा योजना बनाउने मानिसहरूको वास्तविक कथाहरू'"></p>
            </div>
            <div class="marquee overflow-hidden py-4">
                <div class="marquee-content flex gap-8">
                    @php
                        $testimonials = [
                            ['avatar' => 'https://randomuser.me/api/portraits/women/44.jpg', 'comment_en' => 'The AI itinerary saved us so much time! We discovered villages we would never have found.', 'comment_ne' => 'एआई यात्रा योजनाले हाम्रो धेरै समय बचायो! हामीले कहिल्यै नभेट्ने गाउँहरू पत्ता लगायौं।', 'name' => 'Emma Chen', 'rating' => 5],
                            ['avatar' => 'https://randomuser.me/api/portraits/men/32.jpg', 'comment_en' => 'Perfect trek planning. The local food tips were spot on.', 'comment_ne' => 'उत्तम ट्रेक योजना। स्थानीय खानाका सुझावहरू एकदम सही थिए।', 'name' => 'Rajesh Thapa', 'rating' => 5],
                            ['avatar' => 'https://randomuser.me/api/portraits/women/68.jpg', 'comment_en' => 'Easy to use and beautiful destinations. Already planning my next trip.', 'comment_ne' => 'प्रयोग गर्न सजिलो र सुन्दर गन्तव्यहरू। मेरो अर्को यात्रा योजना गर्दैछु।', 'name' => 'Sophie Müller', 'rating' => 4],
                            ['avatar' => 'https://randomuser.me/api/portraits/men/91.jpg', 'comment_en' => 'The AI recommendations for food were incredible! Best momos ever.', 'comment_ne' => 'खानाको लागि एआई सिफारिसहरू अविश्वसनीय थिए! उत्कृष्ट म:म:हरू।', 'name' => 'Alex Johnson', 'rating' => 5],
                        ];
                    @endphp
                    @foreach($testimonials as $t)
                    <div class="flex-shrink-0 w-80 md:w-96 bg-white rounded-2xl shadow-xl p-6 border border-gold/20">
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="{{ $t['avatar'] }}" class="w-12 h-12 rounded-full border-2 border-gold">
                            <div>
                                <p class="font-bold text-forest">{{ $t['name'] }}</p>
                                <div class="flex text-gold text-sm">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="fas fa-star {{ $i <= $t['rating'] ? '' : 'opacity-30' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 italic" x-text="lang === 'en' ? '{{ $t['comment_en'] }}' : '{{ $t['comment_ne'] }}'"></p>
                    </div>
                    @endforeach
                    @foreach($testimonials as $t)
                    <div class="flex-shrink-0 w-80 md:w-96 bg-white rounded-2xl shadow-xl p-6 border border-gold/20">
                        <div class="flex items-center space-x-4 mb-4">
                            <img src="{{ $t['avatar'] }}" class="w-12 h-12 rounded-full border-2 border-gold">
                            <div>
                                <p class="font-bold text-forest">{{ $t['name'] }}</p>
                                <div class="flex text-gold text-sm">
                                    @for($i=1; $i<=5; $i++)
                                        <i class="fas fa-star {{ $i <= $t['rating'] ? '' : 'opacity-30' }}"></i>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        <p class="text-gray-600 italic" x-text="lang === 'en' ? '{{ $t['comment_en'] }}' : '{{ $t['comment_ne'] }}'"></p>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- AI Suggestions -->
    <section class="py-28 bg-gradient-to-b from-cream to-white relative">
        <div class="container mx-auto px-6">
            <div class="text-center mb-16 reveal">
                <h2 class="text-4xl md:text-6xl font-display font-extrabold text-forest" x-text="lang === 'en' ? 'AI‑powered suggestions' : 'एआई-संचालित सुझावहरू'"></h2>
                <p class="text-gray-600 max-w-2xl mx-auto mt-4" x-text="lang === 'en' ? 'Personalized picks for your Nepal adventure' : 'तपाईंको नेपाल साहसिकको लागि व्यक्तिगत छनौटहरू'"></p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                @php
                    $suggestions = [
                        ['img' => 'https://images.unsplash.com/photo-1526392062735-9b0125b1b3b0?w=800&auto=format', 'tag_en' => 'Adventure', 'tag_ne' => 'साहसिक', 'title_en' => 'Trekking & rafting', 'title_ne' => 'ट्रेकिङ र र्याफ्टिङ', 'tip_en' => 'Best time for Annapurna trek is Oct-Nov', 'tip_ne' => 'अन्नपूर्ण ट्रेकको लागि उत्तम समय अक्टोबर-नोभेम्बर हो'],
                        ['img' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&auto=format', 'tag_en' => 'Culture', 'tag_ne' => 'संस्कृति', 'title_en' => 'Temples & festivals', 'title_ne' => 'मन्दिर र चाडपर्वहरू', 'tip_en' => 'Visit during Indra Jatra or Dashain for unique experiences', 'tip_ne' => 'अद्वितीय अनुभवहरूको लागि इन्द्रजात्रा वा दशैंको समयमा भ्रमण गर्नुहोस्'],
                        ['img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&auto=format', 'tag_en' => 'Food', 'tag_ne' => 'खाना', 'title_en' => 'Local cuisine', 'title_ne' => 'स्थानीय खाना', 'tip_en' => 'Try momo, dal bhat, and Newari khaja set', 'tip_ne' => 'म:म:, दाल भात, र नेवारी खाजा सेट प्रयास गर्नुहोस्'],
                    ];
                @endphp
                @foreach($suggestions as $s)
                <div class="group relative rounded-3xl overflow-hidden shadow-2xl tilt-card transition-all duration-300 reveal">
                    <img src="{{ $s['img'] }}" alt="{{ $s['title_en'] }}" class="w-full h-80 object-cover transition-transform duration-700 group-hover:scale-110">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent flex flex-col justify-end p-7 text-white">
                        <span class="bg-gold/90 text-forest text-xs px-3 py-1 rounded-full w-fit mb-2 font-bold" x-text="lang === 'en' ? '{{ $s['tag_en'] }}' : '{{ $s['tag_ne'] }}'"></span>
                        <h3 class="text-2xl font-bold" x-text="lang === 'en' ? '{{ $s['title_en'] }}' : '{{ $s['title_ne'] }}'"></h3>
                        <p class="opacity-0 group-hover:opacity-100 transition-all text-sm mt-2"><i class="fas fa-lightbulb text-gold mr-1"></i> <span x-text="lang === 'en' ? 'AI tip: {{ $s['tip_en'] }}' : 'एआई सुझाव: {{ $s['tip_ne'] }}'"></span></p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Call to Action -->
    <section class="relative py-28 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-forest to-forest/80"></div>
        <div class="absolute inset-0 bg-[url('https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=1920&auto=format')] bg-cover bg-center opacity-20"></div>
        <div class="container mx-auto px-6 text-center relative z-10">
            <h2 class="text-4xl md:text-6xl font-display font-extrabold text-white mb-6" x-text="lang === 'en' ? 'Start Planning Your Trip to Nepal Today!' : 'आजै नेपालको यात्रा योजना सुरु गर्नुहोस्!'"></h2>
            <a href="#" class="inline-flex items-center gap-3 bg-gold text-forest text-xl px-10 py-5 rounded-full font-extrabold shadow-2xl hover:scale-105 transition-all">
                <i class="fas fa-map-marked-alt"></i> <span x-text="lang === 'en' ? 'Plan My Trip' : 'मेरो यात्रा योजना'"></span>
            </a>
        </div>
    </section>
@endsection
