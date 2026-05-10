@extends('layouts.master')

@section('content')
<section class="py-28 bg-cream">
    <div class="container mx-auto px-6">
        <div class="text-center mb-16 reveal">
            <h1 class="text-5xl md:text-7xl font-display font-extrabold text-forest mt-2" x-text="lang === 'en' ? 'Get in Touch' : 'सम्पर्क गर्नुहोस्'"></h1>
            <div class="w-24 h-1 bg-gold mx-auto mt-4 rounded-full"></div>
            <p class="text-gray-600 max-w-2xl mx-auto mt-5 text-lg" x-text="lang === 'en' ? 'We\'d love to hear from you. Reach out with any questions or trip ideas.' : 'हामी तपाईंबाट सुन्न पाउँदा खुशी हुनेछौं। कुनै प्रश्न वा यात्रा विचारहरूको लागि सम्पर्क गर्नुहोस्।'"></p>
        </div>
        <div class="grid md:grid-cols-2 gap-12">
            <!-- Contact Form -->
            <div class="bg-white rounded-3xl shadow-xl p-8 reveal">
                <h3 class="text-2xl font-bold text-forest mb-6" x-text="lang === 'en' ? 'Send a Message' : 'सन्देश पठाउनुहोस्'"></h3>
                <form action="#" method="POST" class="space-y-5">
                    @csrf
                    <div>
                        <label class="block text-gray-700 mb-2" x-text="lang === 'en' ? 'Your Name' : 'तपाईंको नाम'"></label>
                        <input type="text" name="name" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-gold focus:ring-2 focus:ring-gold/20 transition">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" x-text="lang === 'en' ? 'Email Address' : 'इमेल ठेगाना'"></label>
                        <input type="email" name="email" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-gold focus:ring-2 focus:ring-gold/20 transition">
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2" x-text="lang === 'en' ? 'Message' : 'सन्देश'"></label>
                        <textarea name="message" rows="5" class="w-full px-4 py-3 rounded-xl border border-gray-300 focus:border-gold focus:ring-2 focus:ring-gold/20 transition"></textarea>
                    </div>
                    <button type="submit" class="w-full bg-gold text-forest py-3 rounded-xl font-bold hover:bg-amber-400 transition shadow-md" x-text="lang === 'en' ? 'Send Message' : 'सन्देश पठाउनुहोस्'"></button>
                </form>
            </div>

            <!-- Contact Details & Map -->
            <div class="space-y-8">
                <div class="bg-white rounded-3xl shadow-xl p-8 reveal">
                    <h3 class="text-2xl font-bold text-forest mb-6" x-text="lang === 'en' ? 'Contact Information' : 'सम्पर्क जानकारी'"></h3>
                    <div class="space-y-4">
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gold/20 rounded-full flex items-center justify-center text-gold text-xl"><i class="fas fa-map-marker-alt"></i></div>
                            <div>
                                <p class="font-semibold text-gray-800" x-text="lang === 'en' ? 'Address' : 'ठेगाना'"></p>
                                <p class="text-gray-600">Kathmandu, Nepal</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gold/20 rounded-full flex items-center justify-center text-gold text-xl"><i class="fas fa-phone"></i></div>
                            <div>
                                <p class="font-semibold text-gray-800" x-text="lang === 'en' ? 'Phone' : 'फोन'"></p>
                                <p class="text-gray-600">+977 1 2345678</p>
                            </div>
                        </div>
                        <div class="flex items-center space-x-4">
                            <div class="w-12 h-12 bg-gold/20 rounded-full flex items-center justify-center text-gold text-xl"><i class="fas fa-envelope"></i></div>
                            <div>
                                <p class="font-semibold text-gray-800" x-text="lang === 'en' ? 'Email' : 'इमेल'"></p>
                                <p class="text-gray-600">hello@tripplanner.np</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bg-white rounded-3xl shadow-xl overflow-hidden reveal">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3532.317641220896!2d85.32338611493992!3d27.69371488278441!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x39eb19a1c2c1c1c1%3A0x1f2c1c1c1c1c1c1c!2sKathmandu%2C%20Nepal!5e0!3m2!1sen!2snp!4v1643289378936!5m2!1sen!2snp" width="100%" height="300" style="border:0;" allowfullscreen="" loading="lazy"></iframe>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
