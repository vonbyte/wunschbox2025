@props(['title', 'heading' => null])

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-gray-100">

<header class="bg-gray-800 text-white shadow-lg">
    <div class="container mx-auto px-4 py-4 flex justify-between items-center">
        <div>
            <h1 class="text-2xl font-bold">🎄 {{ $heading ?? $title }}</h1>
            @isset($subtitle)
                <p class="text-gray-400 text-sm">{{ $subtitle }}</p>
            @endisset
        </div>
        @isset($actions)
            <div class="flex items-center gap-4">
                {{ $actions }}
            </div>
        @endisset
    </div>
</header>

<div {{ $attributes->merge(['class' => 'container mx-auto px-4 py-8']) }}>
    {{ $slot }}
</div>

{{ $scripts ?? '' }}
</body>
</html>
