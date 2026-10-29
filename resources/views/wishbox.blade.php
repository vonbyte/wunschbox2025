<x-public-layout title="Wunschbox">
    <x-slot:header>
        {{-- Stacked on phones; from lg the title sits left, countdown and subtitle right --}}
        <div class="grid lg:grid-cols-[auto_1fr] lg:items-end lg:gap-x-10">
            <p class="mb-2 inline-flex items-center gap-1.5 text-sm font-bold tracking-wide text-gold-bright lg:col-start-2 lg:row-start-1 lg:mb-1 lg:justify-self-end">
                <svg class="size-4" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                    <path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.1 1.2-6.5L2.5 9.4l6.6-.9z"/>
                </svg>
                <x-christmas-countdown lead="Nur noch" />
            </p>
            <h1 class="-rotate-2 font-hand text-6xl font-bold leading-none text-paper lg:col-start-1 lg:row-span-2 lg:row-start-1 lg:text-7xl">Wunschbox</h1>
            <p class="mt-2 text-pine-muted lg:col-start-2 lg:row-start-2 lg:mt-0 lg:text-right">Santa hat die Rentiere schon vorgewarnt.</p>
        </div>
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
            {{ \App\Models\Wish::owner() }}s Wunschzettel ansehen
        </a>
    </p>
</x-public-layout>
