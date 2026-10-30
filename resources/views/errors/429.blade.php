<x-public-layout :title="__('Take a breath').' · Wunschbox'">
    <x-slot:header>
        <h1 class="-rotate-2 font-hand text-6xl font-bold leading-none text-paper lg:text-7xl">{{ __('Take a breath') }}</h1>
        <p class="mt-2 text-pine-muted">{{ __('The reindeer cannot keep up with the carrying.') }}</p>
    </x-slot:header>

    <p class="text-lg leading-relaxed">
        {{ __('A lot of wishes arrived at once. Please wait a few minutes and try again.') }}
    </p>

    <p class="mt-8">
        <a href="{{ route('wishbox') }}" class="font-bold text-pine underline underline-offset-4 hover:text-pine-dark">
            {{ __('Back to the wishbox') }}
        </a>
    </p>
</x-public-layout>
