<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wunschliste 🎄</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-purple-600 via-pink-500 to-red-500 min-h-screen">
<div class="container mx-auto px-4 py-12">
    <div class="max-w-4xl mx-auto">
        <div class="text-center mb-12">
            <h1 class="text-5xl font-bold text-white mb-3">⭐ Meine Wunschliste ⭐</h1>
            <p class="text-white/80 text-lg">Hier sind meine Weihnachtswünsche</p>
        </div>
        @forelse($wishes as $wish)
            <a href="{{ route('wish.show', $wish) }}" class="block group">
                <div class="bg-white rounded-xl shadow-lg p-6 md:p-8 mb-6 hover:shadow-2xl transition-all duration-200 transform hover:-translate-y-1">

                    @if($wish->image)
                        <div class="mb-4 overflow-hidden rounded-lg">
                            <img
                                src="{{ Storage::url($wish->image) }}"
                                alt="{{ $wish->title }}"
                                class="w-full h-64 object-cover group-hover:scale-105 transition-transform duration-200">
                        </div>
                    @endif

                    <h2 class="text-2xl font-bold text-christmas-red mb-3 group-hover:text-red-700 transition">
                        {{ $wish->title }}
                    </h2>

                    @if($wish->description)
                        <p class="text-gray-700 leading-relaxed mb-4 line-clamp-3">
                            {{ $wish->description }}
                        </p>
                    @endif

                    <!-- "View More" indicator -->
                    <div class="flex items-center text-christmas-red font-semibold group-hover:translate-x-2 transition">
                        Details ansehen
                        <svg class="w-5 h-5 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white rounded-xl shadow-lg p-12 text-center">
                <div class="text-6xl mb-4">🎁</div>
                <h2 class="text-2xl font-semibold text-gray-800 mb-2">Die Wunschliste ist noch leer</h2>
                <p class="text-gray-600">Schau später wieder vorbei!</p>
            </div>
        @endforelse
        <div class="text-center mt-8">
            <a href="{{ route('wishbox') }}" class="inline-block bg-white hover:bg-gray-100 text-christmas-red font-bold py-3 px-8 rounded-lg transition shadow-lg">
                ← Zurück zur Wunschbox
            </a>
        </div>
    </div>
</div>
</body>
</html>
