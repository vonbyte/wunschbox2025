@php($owner = config('wishbox.owner'))

<x-public-layout :title="$owner.'s Wunschzettel'">
    <x-slot:header>
        <h1 class="-rotate-2 font-hand text-6xl font-bold leading-none text-paper">Mein Wunschzettel</h1>
    </x-slot:header>

    @if($wishes->isEmpty())
        <div class="py-10 text-center">
            <p class="font-hand text-4xl text-pine">Der Wunschzettel ist noch leer.</p>
            <p class="mt-2 text-ink-muted">Schau später wieder vorbei.</p>
        </div>
    @else
        <p class="mb-4 font-hand text-3xl text-pine">Lieber Santa, ich wünsche mir …</p>

        <ol>
            @foreach($wishes as $wish)
                @php($linkCount = count($wish->example_links ?? []))
                <li class="border-b-[1.5px] border-line">
                    <a href="{{ route('wish.show', $wish) }}" class="group flex items-start gap-3 py-3 focus-visible:outline-2 focus-visible:outline-pine">
                        <span class="w-8 shrink-0 font-hand text-3xl font-bold text-berry">{{ $loop->iteration }}.</span>
                        <span class="min-w-0 flex-1">
                            <span class="block font-hand text-3xl font-bold leading-tight text-ink group-hover:text-pine">{{ $wish->title }}</span>
                            @if($wish->description || $linkCount)
                                <span class="mt-0.5 block text-sm text-ink-muted">
                                    @if($wish->description)
                                        <span class="line-clamp-1">{{ $wish->description }}</span>
                                    @endif
                                    @if($linkCount)
                                        <span class="inline-flex items-center gap-1 text-pine">
                                            <svg class="size-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M10 14a4 4 0 005.66 0l3-3a4 4 0 00-5.66-5.66l-1 1M14 10a4 4 0 00-5.66 0l-3 3a4 4 0 005.66 5.66l1-1"/>
                                            </svg>
                                            {{ $linkCount }} {{ $linkCount === 1 ? 'Link' : 'Links' }}
                                        </span>
                                    @endif
                                </span>
                            @endif
                        </span>
                        @if($wish->image)
                            <span class="polaroid shrink-0" style="--tilt: {{ $loop->odd ? '4deg' : '-3deg' }}">
                                <img src="{{ Storage::url($wish->image_thumbnail ?? $wish->image) }}"
                                     alt=""
                                     class="h-12 w-14 object-cover"
                                     loading="lazy">
                            </span>
                        @endif
                    </a>
                </li>
            @endforeach
        </ol>

        <p class="mt-6 text-right font-hand text-3xl text-pine">
            Deine {{ $owner }}
            <svg class="inline size-5 -translate-y-0.5 text-berry" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 21s-7.5-4.6-9.5-9.2C1.2 8.6 3.2 5 6.7 5c2 0 3.6 1.1 4.3 2.6h2C13.7 6.1 15.3 5 17.3 5c3.5 0 5.5 3.6 4.2 6.8C19.5 16.4 12 21 12 21z"/>
            </svg>
        </p>
    @endif

    <p class="mt-10 text-center">
        <a href="{{ route('wishbox') }}" class="font-bold text-pine underline underline-offset-4 hover:text-pine-dark">
            Selbst einen Wunsch einwerfen
        </a>
    </p>
</x-public-layout>
