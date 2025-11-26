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
            <a href="{{ route('wish.show', $wish) }}" class="block group mb-4">
                <div class="bg-white rounded-lg shadow-md hover:shadow-xl transition-shadow duration-200 p-4">

                    <div class="flex gap-4">

                        <!-- Small Thumbnail (120x90px) -->
                        <div class="flex-shrink-0">
                            @if($wish->image)
                                <div class="w-32 h-32 rounded-lg overflow-hidden bg-gray-100">
                                    <img
                                        src="{{ Storage::url($wish->image_thumbnail ?? $wish->image) }}"
                                        alt="{{ $wish->title }}"
                                        class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-200"
                                        loading="lazy">
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-32 h-24 text-blue-400 opacity-40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 2v20M2 12h20M6 6l12 12M18 6L6 18"/>
                                        <circle cx="12" cy="12" r="2" fill="currentColor"/>
                                        <circle cx="12" cy="4" r="1.5" fill="currentColor"/>
                                        <circle cx="12" cy="20" r="1.5" fill="currentColor"/>
                                        <circle cx="4" cy="12" r="1.5" fill="currentColor"/>
                                        <circle cx="20" cy="12" r="1.5" fill="currentColor"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="flex-1 min-w-0">
                            <h2 class="text-xl font-bold text-christmas-red mb-2 group-hover:text-red-700 transition line-clamp-1">
                                {{ $wish->title }}
                            </h2>

                            @if($wish->description)
                                <p class="text-gray-600 text-sm leading-relaxed line-clamp-2 mb-2">
                                    {{ $wish->description }}
                                </p>
                            @endif

                            <div class="flex items-center text-christmas-red text-sm font-medium group-hover:translate-x-1 transition">
                                Details ansehen
                                <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </div>
                        </div>
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
