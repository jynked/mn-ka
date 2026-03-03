@extends('layouts.public')

@section('title', 'Главная страница')

@section('content')
<!-- Hero Banner -->
<section class="relative w-full min-h-[400px] sm:min-h-[500px] md:min-h-[600px] lg:min-h-[700px] overflow-hidden">
    <!-- Background Image -->
    <div class="absolute inset-0 bg-cover bg-center bg-no-repeat bg-[#6C7B6D]" style="background-image: url('/upload/hero/hero-banner.jpg'); background-size: cover; background-position: center;">
        <!-- Semi-transparent dark green overlay -->
        <div class="absolute inset-0 bg-[#6C7B6D]/75"></div>
    </div>
    
    <!-- Content Overlay -->
    <div class="relative z-10 container mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8 h-full min-h-[400px] sm:min-h-[500px] md:min-h-[600px] lg:min-h-[700px] flex items-center">
        <div class="w-full max-w-2xl">
            <div class="space-y-2 sm:space-y-3 mb-6 sm:mb-8">
                <h1 class="text-white text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-semibold leading-tight">
                    Подберём и оформим
                </h1>
                <h2 class="text-white text-2xl sm:text-3xl md:text-4xl lg:text-5xl xl:text-6xl font-semibold leading-tight">
                    участок под ваш проект
                </h2>
                <p class="text-white text-lg sm:text-xl md:text-2xl lg:text-3xl font-normal leading-relaxed">
                    — от ИЖС до складов<br>
                    и ритейла
                </p>
            </div>
            <a 
                href="#services" 
                class="inline-block bg-[#6C7B6D] hover:bg-[#5a696b] active:bg-[#4d585a] text-white font-medium text-base sm:text-lg px-8 sm:px-10 md:px-12 py-3 sm:py-3.5 md:py-4 rounded-lg transition-all duration-200 transform hover:scale-[1.02] active:scale-100 shadow-lg"
            >
                Подробнее
            </a>
        </div>
    </div>
</section>

<!-- Main Content -->
<div class="container mx-auto max-w-[1200px] px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="text-center">
        <h2 class="text-2xl sm:text-3xl md:text-4xl font-bold mb-4">Добро пожаловать</h2>
        <p class="text-lg text-gray-600 mb-8">Это публичная главная страница</p>
    </div>
</div>
@endsection

