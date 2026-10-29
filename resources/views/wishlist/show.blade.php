<x-public-layout :title="$wish->title.' · Wunschzettel'">
    <x-slot:header>
        <a href="{{ auth()->check() ? route('admin.index') : route('wishlist') }}"
           class="mb-3 inline-flex items-center gap-1.5 text-sm font-bold text-pine-muted hover:text-white">
            <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7 7-7M3 12h18"/>
            </svg>
            {{ auth()->check() ? 'Zurück zum Admin' : 'Zurück zum Wunschzettel' }}
        </a>
        <h1 class="-rotate-1 font-hand text-5xl font-bold leading-none text-paper lg:text-6xl">{{ $wish->title }}</h1>
    </x-slot:header>

    <article>
        @if($wish->image)
            <div class="mb-8 text-center">
                <span class="polaroid p-2 pb-8" style="--tilt: -2deg">
                    <img src="{{ Storage::url($wish->image) }}"
                         alt="{{ $wish->title }}"
                         class="max-h-[28rem] w-auto object-contain"
                         loading="eager">
                </span>
            </div>
        @endif

        @if($wish->description)
            <p class="whitespace-pre-line text-lg leading-relaxed">{{ $wish->description }}</p>
        @endif

        @if($wish->example_links && count($wish->example_links) > 0)
            <h2 class="label-caps mt-8 mb-3">Beispiele</h2>
            <ul class="space-y-2">
                @foreach($wish->example_links as $link)
                    <li>
                        <a href="{{ $link }}" target="_blank" rel="noopener noreferrer"
                           class="card flex items-center gap-3 px-4 py-3 transition hover:border-pine focus-visible:outline-2 focus-visible:outline-pine">
                            <svg class="size-5 shrink-0 text-pine" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            <span class="min-w-0 flex-1">
                                <span class="block font-bold">{{ Str::after(parse_url($link, PHP_URL_HOST) ?? $link, 'www.') }}</span>
                                <span class="block truncate text-sm text-ink-muted">{{ $link }}</span>
                            </span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif

        <!-- Admin Actions -->
        @auth
            @if(auth()->user()->isAdmin())
                <div class="mt-10 flex flex-wrap items-center gap-2 border-t border-dashed border-line pt-5">
                    <span class="mr-2 inline-flex items-center gap-1 text-xs font-bold text-ink-muted">
                        <svg class="size-3.5" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                        Nur Admin · von {{ $wish->receiver }} · {{ $wish->isOnWishNote() ? 'Auf dem Wunschzettel' : 'Nicht öffentlich' }}
                    </span>
                    <a href="{{ route('admin.wishes.edit', $wish) }}" class="btn btn-ghost">Bearbeiten</a>
                    @if($wish->canTogglePublic())
                        <form method="POST" action="{{ route('admin.wishes.toggle', $wish) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="btn btn-ghost">
                                {{ $wish->is_public ? 'Privat machen' : 'Auf den Wunschzettel' }}
                            </button>
                        </form>
                    @endif
                    <form method="POST" action="{{ route('admin.wishes.destroy', $wish) }}" onsubmit="return confirm('Wirklich löschen?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn btn-danger">Löschen</button>
                    </form>
                </div>
            @endif
        @endauth

        <p class="mt-8 text-sm text-ink-muted">Aufgeschrieben am {{ $wish->created_at->format('d.m.Y') }}</p>
    </article>
</x-public-layout>
