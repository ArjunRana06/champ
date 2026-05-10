@extends('layouts.master')

@section('content')
<section class="py-28 bg-cream relative">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16 reveal">
            <h1 class="text-5xl md:text-7xl font-display font-extrabold text-forest mt-2" x-text="lang === 'en' ? 'All Destinations' : 'सबै गन्तव्यहरू'"></h1>
            <div class="w-24 h-1 bg-gold mx-auto mt-4 rounded-full"></div>
            <p class="text-gray-600 max-w-2xl mx-auto mt-5 text-lg" x-text="lang === 'en' ? 'Discover the best places to visit in Nepal, from cultural hubs to natural wonders.' : 'सांस्कृतिक केन्द्रदेखि प्राकृतिक चमत्कारहरूसम्म, नेपालमा भ्रमण गर्नका लागि उत्तम स्थानहरू पत्ता लगाउनुहोस्।'"></p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-10">
            @php
                $allDestinations = [
                    ['name_en' => 'Kathmandu', 'name_ne' => 'काठमाडौं', 'img' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&auto=format', 'type_en' => 'Culture', 'type_ne' => 'संस्कृति', 'desc_en' => 'Capital city with ancient temples, bustling streets, and UNESCO World Heritage Sites.', 'desc_ne' => 'प्राचीन मन्दिरहरू, व्यस्त सडकहरू, र युनेस्को विश्व सम्पदा स्थलहरू सहितको राजधानी सहर।', 'overlay_en' => 'Durbar Square · Swayambhunath · Pashupatinath', 'overlay_ne' => 'दरबार स्क्वायर · स्वयम्भूनाथ · पशुपतिनाथ', 'icon' => 'fa-map-pin'],
                    ['name_en' => 'Pokhara', 'name_ne' => 'पोखरा', 'img' => 'https://images.unsplash.com/photo-1526392062735-9b0125b1b3b0?w=800&auto=format', 'type_en' => 'Nature', 'type_ne' => 'प्रकृति', 'desc_en' => 'Gateway to the Annapurna Circuit, serene lakes, and adventure sports.', 'desc_ne' => 'अन्नपूर्ण सर्किटको प्रवेशद्वार, शान्त तालहरू, र साहसिक खेलहरू।', 'overlay_en' => 'Phewa Lake · Sarangkot · Paragliding', 'overlay_ne' => 'फेवा ताल · साराङकोट · प्याराग्लाइडिङ', 'icon' => 'fa-parachute-box'],
                    ['name_en' => 'Chitwan', 'name_ne' => 'चितवन', 'img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&auto=format', 'type_en' => 'Wildlife', 'type_ne' => 'वन्यजन्तु', 'desc_en' => 'Home to one-horned rhinos, Bengal tigers, and lush national parks.', 'desc_ne' => 'एकसिङ्गे गैंडा, बङ्गाल बाघ, र हरियाली राष्ट्रिय निकुञ्जको घर।', 'overlay_en' => 'Jungle safari · Elephant breeding center · Bird watching', 'overlay_ne' => 'जंगल सफारी · हात्ती प्रजनन केन्द्र · चरा अवलोकन', 'icon' => 'fa-paw'],
                    ['name_en' => 'Lumbini', 'name_ne' => 'लुम्बिनी', 'img' => 'https://images.unsplash.com/photo-1580043621114-6721db12b9a1?w=800&auto=format', 'type_en' => 'Pilgrimage', 'type_ne' => 'तीर्थस्थल', 'desc_en' => 'Birthplace of Lord Buddha, a sacred site for Buddhists worldwide.', 'desc_ne' => 'भगवान बुद्धको जन्मस्थल, विश्वभरका बौद्धहरूको लागि पवित्र स्थल।', 'overlay_en' => 'Maya Devi Temple · Ashokan Pillar · Monasteries', 'overlay_ne' => 'माया देवी मन्दिर · अशोक स्तम्भ · मठहरू', 'icon' => 'fa-dharmachakra'],
                    ['name_en' => 'Everest Region', 'name_ne' => 'सगरमाथा क्षेत्र', 'img' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&auto=format', 'type_en' => 'Adventure', 'type_ne' => 'साहसिक', 'desc_en' => 'Home to the world\'s highest peak, legendary treks, and Sherpa culture.', 'desc_ne' => 'विश्वको सर्वोच्च शिखर, पौराणिक ट्रेकहरू, र शेर्पा संस्कृतिको घर।', 'overlay_en' => 'Everest Base Camp · Kala Patthar · Lukla', 'overlay_ne' => 'सगरमाथा आधार शिविर · कालापत्थर · लुक्ला', 'icon' => 'fa-mountain'],
                    ['name_en' => 'Annapurna Region', 'name_ne' => 'अन्नपूर्ण क्षेत्र', 'img' => 'https://images.unsplash.com/photo-1506905925346-21bda4d32df4?w=800&auto=format', 'type_en' => 'Trekking', 'type_ne' => 'ट्रेकिङ', 'desc_en' => 'Diverse landscapes, from lush forests to high-altitude deserts.', 'desc_ne' => 'हरियाली जंगलदेखि उच्च-उचाइको मरुभूमिसम्म विविध परिदृश्यहरू।', 'overlay_en' => 'Annapurna Circuit · Poon Hill · Tilicho Lake', 'overlay_ne' => 'अन्नपूर्ण सर्किट · पून हिल · तिलिचो ताल', 'icon' => 'fa-hiking'],
                    ['name_en' => 'Bandipur', 'name_ne' => 'बन्दीपुर', 'img' => 'https://images.unsplash.com/photo-1544735716-392fe2489ffa?w=800&auto=format', 'type_en' => 'Culture', 'type_ne' => 'संस्कृति', 'desc_en' => 'A charming hilltop town with Newari architecture and panoramic mountain views.', 'desc_ne' => 'नेवारी वास्तुकला र मनोरम हिमाली दृश्यहरू सहितको आकर्षक पहाडी सहर।', 'overlay_en' => 'Thani Mai Temple · Bindyabasini Temple · Caves', 'overlay_ne' => 'थानी माई मन्दिर · बिन्द्यबासिनी मन्दिर · गुफाहरू', 'icon' => 'fa-archway'],
                    ['name_en' => 'Ilam', 'name_ne' => 'इलाम', 'img' => 'https://images.unsplash.com/photo-1526392062735-9b0125b1b3b0?w=800&auto=format', 'type_en' => 'Nature', 'type_ne' => 'प्रकृति', 'desc_en' => 'Tea gardens, rolling hills, and pristine forests in eastern Nepal.', 'desc_ne' => 'पूर्वी नेपालमा चिया बगान, पहाडी भूभाग, र अक्षुण्ण वनहरू।', 'overlay_en' => 'Kanyam Tea Garden · Fikkal Bazaar · Mai Pokhari', 'overlay_ne' => 'कन्याम चिया बगान · फिक्कल बजार · माई पोखरी', 'icon' => 'fa-leaf'],
                ];
            @endphp
            @foreach($allDestinations as $d)
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
@endsection
