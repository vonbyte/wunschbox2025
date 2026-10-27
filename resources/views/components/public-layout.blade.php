@props(['title'])

<!DOCTYPE html>
<html lang="de">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen lg:bg-paper-dark">

<header class="bg-pine">
    <x-fairy-lights />
    <div class="mx-auto max-w-2xl px-6 pb-6 lg:max-w-3xl lg:pb-10 xl:max-w-4xl">
        {{ $header }}
    </div>
</header>
<div class="twine"></div>

<main {{ $attributes->merge(['class' => 'mx-auto max-w-2xl px-6 py-8 lg:max-w-3xl lg:py-14 xl:max-w-4xl']) }}>
    <div class="sheet">
        {{ $slot }}
    </div>
</main>

{{ $scripts ?? '' }}
</body>
</html>
