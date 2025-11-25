<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $wish->title }} - Wishbox 🎄</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-purple-600 via-pink-500 to-red-500 min-h-screen">

<div class="container mx-auto px-4 py-12">
    <div class="max-w-3xl mx-auto">

        <!-- Back Button -->
        <div class="mb-6">
            <a href="{{ auth()->check() ? route('admin.index') : route('wishlist') }}"
               class="inline-flex items-center text-white hover:text-gray-200 transition">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Zurück
            </a>
        </div>

        <!-- Wish Detail Card -->
        <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">

            <!-- Image (if exists) -->
            @if($wish->image)
                <div class="relative">
                    <img
                        src="{{ Storage::url($wish->image) }}"
                        alt="{{ $wish->title }}"
                        class="w-full h-96 object-cover">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/50 to-transparent"></div>
                </div>
            @endif

            <!-- Content -->
            <div class="p-8 md:p-12">

                <!-- Status Badge (for admin) -->
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="mb-4">
                            @if($wish->is_public)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-green-100 text-green-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                        <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Öffentlich
                                </span>
                            @else
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold bg-gray-100 text-gray-800">
                                    <svg class="w-4 h-4 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                                    </svg>
                                    Privat
                                </span>
                            @endif
                        </div>
                    @endif
                @endauth

                <!-- Title -->
                <h1 class="text-4xl md:text-5xl font-bold text-christmas-red mb-4">
                    {{ $wish->title }}
                </h1>

                <!-- Receiver -->
                <div class="flex items-center text-gray-600 mb-6">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-medium">{{ $wish->receiver }}</span>
                </div>

                <!-- Description -->
                @if($wish->description)
                    <div class="prose prose-lg max-w-none mb-8">
                        <p class="text-gray-700 leading-relaxed whitespace-pre-line">{{ $wish->description }}</p>
                    </div>
                @endif

                <!-- Example Links -->
                @if($wish->example_links && count($wish->example_links) > 0)
                    <div class="mt-8 pt-8 border-t-2 border-gray-100">
                        <h2 class="text-2xl font-bold text-gray-800 mb-4 flex items-center">
                            <svg class="w-6 h-6 mr-2 text-christmas-red" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Beispiel-Links
                        </h2>
                        <div class="space-y-3">
                            @foreach($wish->example_links as $index => $link)
                                <a
                                    href="{{ $link }}"
                                    target="_blank"
                                    rel="noopener noreferrer"
                                    class="block group">
                                    <div class="flex items-center p-4 bg-gray-50 hover:bg-christmas-red hover:text-white rounded-lg transition duration-200">
                                        <div class="flex-shrink-0 w-10 h-10 bg-christmas-red group-hover:bg-white rounded-lg flex items-center justify-center mr-4 transition">
                                            <svg class="w-5 h-5 text-white group-hover:text-christmas-red transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                            </svg>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <p class="font-medium truncate">
                                                Beispiel {{ $index + 1 }}
                                            </p>
                                            <p class="text-sm truncate opacity-75">
                                                {{ $link }}
                                            </p>
                                        </div>
                                        <svg class="w-5 h-5 flex-shrink-0 ml-2 opacity-50 group-hover:opacity-100 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Admin Actions -->
                @auth
                    @if(auth()->user()->isAdmin())
                        <div class="mt-8 pt-8 border-t-2 border-gray-100">
                            <div class="flex flex-wrap gap-3">
                                <a
                                    href="{{ route('admin.wishes.edit', $wish) }}"
                                    class="inline-flex items-center px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg transition">
                                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                    Bearbeiten
                                </a>

                                <form method="POST" action="{{ route('admin.wishes.toggle', $wish) }}" class="inline">
                                    @csrf @method('PATCH')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-6 py-3 bg-orange-600 hover:bg-orange-700 text-white font-medium rounded-lg transition">
                                        @if($wish->is_public)
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                            Privat machen
                                        @else
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Öffentlich machen
                                        @endif
                                    </button>
                                </form>

                                <form
                                    method="POST"
                                    action="{{ route('admin.wishes.destroy', $wish) }}"
                                    class="inline"
                                    onsubmit="return confirm('Wirklich löschen?')">
                                    @csrf @method('DELETE')
                                    <button
                                        type="submit"
                                        class="inline-flex items-center px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-medium rounded-lg transition">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                        Löschen
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif
                @endauth

                <!-- Created Date -->
                <div class="mt-8 pt-6 border-t border-gray-100 text-sm text-gray-500">
                    Erstellt am {{ $wish->created_at->format('d.m.Y') }}
                </div>
            </div>
        </div>
    </div>
</div>
</body>
</html>
