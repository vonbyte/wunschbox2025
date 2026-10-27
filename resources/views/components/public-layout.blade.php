@props(['title'])

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen">

<header class="bg-pine">
    <x-fairy-lights />
    <div class="mx-auto max-w-2xl px-6 pb-6">
        {{ $header }}
    </div>
</header>
<div class="twine"></div>

<main {{ $attributes->merge(['class' => 'mx-auto max-w-2xl px-6 py-8']) }}>
    {{ $slot }}
</main>

{{ $scripts ?? '' }}
</body>
</html>
