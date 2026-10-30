<x-public-layout title="Kurz durchatmen · Wunschbox">
    <x-slot:header>
        <h1 class="-rotate-2 font-hand text-6xl font-bold leading-none text-paper lg:text-7xl">Kurz durchatmen</h1>
        <p class="mt-2 text-pine-muted">Die Rentiere kommen mit dem Tragen nicht nach.</p>
    </x-slot:header>

    <p class="text-lg leading-relaxed">
        Es sind gerade sehr viele Wünsche auf einmal angekommen. Bitte warte ein paar Minuten und versuch es dann noch einmal.
    </p>

    <p class="mt-8">
        <a href="{{ route('wishbox') }}" class="font-bold text-pine underline underline-offset-4 hover:text-pine-dark">
            Zurück zur Wunschbox
        </a>
    </p>
</x-public-layout>
