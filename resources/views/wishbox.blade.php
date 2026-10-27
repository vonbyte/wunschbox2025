<x-public-layout title="Wunschbox">
    <x-slot:header>
        <h1 class="-rotate-2 font-hand text-6xl font-bold leading-none text-paper">Wunschbox</h1>
        <p class="mt-2 text-pine-muted">Schreib deinen Wunsch auf, wir werfen ihn in die Box.</p>
    </x-slot:header>

    @if(session('success'))
        <div class="flash flex items-center gap-3" role="status">
            <svg class="size-6 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
            </svg>
            <p>{{ session('success') }}</p>
        </div>
    @endif

    <x-wish-form
        :action="route('wishbox.store')"
        submit-text="Ab in die Box!"
    />

    <p class="mt-10 text-center">
        <a href="{{ route('wishlist') }}" class="font-bold text-pine underline underline-offset-4 hover:text-pine-dark">
            {{ config('wishbox.owner') }}s Wunschzettel ansehen
        </a>
    </p>
</x-public-layout>
