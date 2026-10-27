@props(['title', 'heading' => null])

@php
    $christmas = now()->setDate(now()->year, 12, 24)->startOfDay();
    if (now()->startOfDay()->gt($christmas)) {
        $christmas->addYear();
    }
    $daysToChristmas = (int) now()->startOfDay()->diffInDays($christmas);
@endphp

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
    <x-fairy-lights size="sm" />
    <div class="container mx-auto flex flex-wrap items-end justify-between gap-4 px-4 pb-4">
        <div>
            <p class="font-hand text-4xl font-bold leading-none text-paper">{{ $heading ?? $title }}</p>
            <p class="mt-1 text-sm text-pine-muted">
                @isset($subtitle)
                    {{ $subtitle }} ·
                @endisset
                {{ $daysToChristmas === 0 ? 'Heute ist Heiligabend' : "noch {$daysToChristmas} ".($daysToChristmas === 1 ? 'Tag' : 'Tage').' bis Weihnachten' }}
            </p>
        </div>
        <nav class="flex flex-wrap items-center gap-4 text-sm font-bold text-pine-muted">
            {{ $actions ?? '' }}
            <a href="{{ route('admin.index') }}" class="hover:text-white @if(request()->routeIs('admin.index')) text-white @endif">Werkstatt</a>
            <a href="{{ route('wishlist') }}" class="hover:text-white">Wunschzettel</a>
            <a href="{{ route('profile.edit') }}" class="hover:text-white @if(request()->routeIs('profile.edit')) text-white @endif">Profil</a>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg border border-pine-muted/40 px-3 py-1 text-white transition hover:bg-pine-dark">
                    Abmelden
                </button>
            </form>
        </nav>
    </div>
</header>
<div class="twine"></div>

<main {{ $attributes->merge(['class' => 'container mx-auto px-4 py-8']) }}>
    @if(session('success'))
        <div class="flash" role="status">{{ session('success') }}</div>
    @endif

    {{ $slot }}
</main>

{{ $scripts ?? '' }}
</body>
</html>
