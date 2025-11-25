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
        <x-wish-form
            :action="route('wishbox.store')"
            submit-text="🎁 Wunsch absenden"
        />
        <div class="mt-8 text-center">
            <a href="{{ route('wishlist') }}" class="text-christmas-red hover:text-red-700 font-semibold">
                📜 Zur Wunschliste →
            </a>
        </div>
    </div>
</div>
</body>
</html>
