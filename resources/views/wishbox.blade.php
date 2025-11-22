<!doctype html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Wunschbox</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gradient-to-br from-red-700 via-red-950 to-green-950 min-h-screen">
<!-- Snowflakes -->
@for($i=0;$i<15;$i++)
    <div class="snowflake"
         style="left: {{$i * 6.66}}%; animation-duration: {{random_int(10,20)}}s;animation-delay: {{random_int(0,5)}}s">❄
    </div>
@endfor
<div class="container mx-auto px-4 py-12">
    <div class="max-w-2xl mx-auto bg-white rounded-3xl shadow-2xl p-8 md:p-12">
        <div class="text-center mb-8">
            <h1 class="text-5xl font-bold text-christmas-red mb-3">🎄 Wunschbox 🎄</h1>
            <p class="text-gray-600 text-lg">Rein mit den Wünschen!</p>
        </div>
        @if(session('success'))
            <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded">
                <p class="font-semibold">{{ session('success') }}</p>
            </div>
        @endif
        <form method="POST" action="{{ route('wishbox.store') }}" class="space-y-6">
            @csrf
            <div>
                <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
                    Was wünschst du dir? *
                </label>
                <input
                    type="text"
                    id="title"
                    name="title"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition"
                    placeholder="z.B. Kuscheldecke, Buch, Kopfhörer...">
                @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="receiver" class="block text-sm font-medium text-gray-700 mb-2">
                    Dein Name *
                </label>
                <select
                    id="receiver"
                    name="receiver"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition">
                    <option value="sonia" selected="selected">Sonia</option>
                    <option value="other">Andere</option>
                </select>
                @error('receiver')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                    Bild (optional)
                </label>
                <input
                    type="file"
                    id="image"
                    name="image"
                    required
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition">
                @error('title')
                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
                    Beschreibung (optional)
                </label>
                <textarea
                    id="description"
                    name="description"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition resize-none"
                    placeholder="Erzähl mehr über deinen Wunsch..."></textarea>
            </div>

            <div>
                <label for="example_links" class="block text-sm font-medium text-gray-700 mb-2">
                    Links (optional)
                </label>
                <textarea
                    id="example_links"
                    name="example_links"
                    rows="4"
                    class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition resize-none"
                    placeholder="Gib ein paar Beispielseiten an..."></textarea>
            </div>
            <button
                type="submit"
                class="w-full bg-christmas-red hover:bg-red-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 transform hover:scale-105 shadow-lg">
                🎁 Wunsch absenden
            </button>
        </form>
        <div class="mt-8 text-center">
            <a href="{{ route('wishlist') }}" class="text-christmas-red hover:text-red-700 font-semibold">
                📜 Zur Wunschliste →
            </a>
        </div>
    </div>
</div>
</body>
</html>
