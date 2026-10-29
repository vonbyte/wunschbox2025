@php
    $filters = [
        null => ['label' => 'Alle', 'count' => $counts['all'], 'color' => 'text-pine'],
        'open' => ['label' => 'Offen', 'count' => $counts['open'], 'color' => 'text-blue-700'],
        'delivered' => ['label' => 'Geliefert', 'count' => $counts['delivered'], 'color' => 'text-orange-700'],
        'packed' => ['label' => 'Eingepackt', 'count' => $counts['packed'], 'color' => 'text-green-700'],
        'unpacked' => ['label' => 'Ausgepackt', 'count' => $counts['unpacked'], 'color' => 'text-purple-700'],
    ];
    $rowGrid = "grid grid-cols-[3rem_minmax(0,1fr)_auto] [grid-template-areas:'thumb_title_status'_'nums_nums_actions'] md:grid-cols-[3rem_minmax(0,1fr)_4.5rem_4.5rem_8.5rem_7rem] md:[grid-template-areas:'thumb_title_nums_nums_status_actions'] items-center gap-x-3 gap-y-2";
    $myRowGrid = 'grid grid-cols-[4.5rem_3rem_minmax(0,1fr)_auto] items-center gap-x-3';
@endphp

<x-admin-layout title="Wunschbox Admin" heading="Wunschbox · Werkstatt">
    <x-slot:subtitle>Hallo {{ auth()->user()->name }}</x-slot:subtitle>

    {{-- Errors of the add form are in their own bag and show inside the form --}}
    @if($errors->any())
        <div class="flash-error" role="alert">
            Nicht gespeichert: {{ $errors->first() }}
        </div>
    @endif

    <!-- Guest Wishes -->
    <section aria-labelledby="guest-wishes">
        <h1 id="guest-wishes" class="mb-4 font-hand text-4xl font-bold leading-none text-berry">Gäste-Wünsche</h1>

        <nav aria-label="Filter" class="mb-4 grid grid-cols-5 gap-1.5 sm:gap-2">
            @foreach($filters as $key => $item)
                @php($active = $filter === ($key ?: null))
                <a href="{{ route('admin.index', $key ? ['filter' => $key] : []) }}"
                   @if($active) aria-current="page" @endif
                   class="min-w-0 rounded-xl border px-2 py-1.5 transition sm:px-3 sm:py-2 focus-visible:outline-2 focus-visible:outline-pine
                      {{ $active ? 'border-pine bg-pine text-white' : 'border-line bg-white hover:border-pine' }}">
                    <span class="block truncate text-[11px] font-bold sm:text-xs {{ $active ? 'text-pine-muted' : $item['color'] }}">{{ $item['label'] }}</span>
                    <span class="block text-xl font-bold leading-tight sm:text-2xl">{{ $item['count'] }}</span>
                </a>
            @endforeach
        </nav>

        <div class="card overflow-hidden">
            @if($guestWishes->isEmpty())
                <p class="px-4 py-6 text-ink-muted">Noch keine Wünsche von Gästen …</p>
            @else
                <div class="{{ $rowGrid }} hidden border-b border-line px-4 py-2 text-xs font-bold tracking-wider text-ink-muted uppercase md:grid" aria-hidden="true">
                    <span class="[grid-area:thumb]"></span>
                    <span class="[grid-area:title]">Wunsch</span>
                    <span class="[grid-area:nums] grid grid-cols-2 gap-3"><span>Paket</span><span>Sort.</span></span>
                    <span class="[grid-area:status]">Status</span>
                    <span class="[grid-area:actions]"></span>
                </div>
                <ul class="divide-y divide-line">
                    @foreach($guestWishes as $wish)
                        <li class="{{ $rowGrid }} px-4 py-3 hover:bg-paper/60">
                            <!-- Image -->
                            <a href="{{ route('wish.show', $wish) }}" class="[grid-area:thumb]" tabindex="-1" aria-hidden="true">
                                <x-wish-thumb :wish="$wish" :tilt="$loop->odd ? '-3deg' : '2deg'" />
                            </a>

                            <!-- Title and receiver -->
                            <div class="[grid-area:title] min-w-0">
                                <a href="{{ route('wish.show', $wish) }}" class="font-bold hover:text-pine">{{ $wish->title }}</a>
                                <p class="truncate text-sm text-ink-muted">
                                    von {{ $wish->receiver }}@if($wish->description) · {{ $wish->description }}@endif
                                </p>
                            </div>

                            <!-- Package Nr and Sort Nr -->
                            <div class="[grid-area:nums] grid grid-cols-2 gap-3 max-md:flex max-md:gap-4">
                                <form method="POST" action="{{ route('admin.wishes.update-nr', $wish) }}">
                                    @csrf @method('PATCH')
                                    <label class="flex items-center gap-1.5 text-xs font-bold text-ink-muted">
                                        <span class="md:sr-only">Paket</span>
                                        <input type="number" name="package_nr" value="{{ $wish->package_nr }}" min="0" step="1" required
                                               class="field-box w-16 px-2 py-1 text-center text-sm"
                                               onchange="this.form.requestSubmit()">
                                    </label>
                                </form>
                                <form method="POST" action="{{ route('admin.wishes.update-sort', $wish) }}">
                                    @csrf @method('PATCH')
                                    <label class="flex items-center gap-1.5 text-xs font-bold text-ink-muted">
                                        <span class="md:sr-only">Sort.</span>
                                        <input type="number" name="sortnr" value="{{ $wish->sortnr }}" min="0" step="1" required
                                               class="field-box w-16 px-2 py-1 text-center text-sm"
                                               onchange="this.form.requestSubmit()">
                                    </label>
                                </form>
                            </div>

                            <!-- Status -->
                            <form method="POST" action="{{ route('admin.wishes.update-status', $wish) }}" class="[grid-area:status]">
                                @csrf @method('PATCH')
                                <select name="status" aria-label="Status von {{ $wish->title }}"
                                        onchange="this.form.submit()"
                                        class="state-{{ $wish->status_color }} cursor-pointer rounded-full border-0 py-1 pr-2 pl-3 text-xs font-bold">
                                    @foreach(\App\Models\Wish::getStatuses() as $value => $label)
                                        <option value="{{ $value }}" @selected($wish->status === $value)>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </form>

                            <!-- Actions -->
                            <x-wish-actions :wish="$wish" class="[grid-area:actions]" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>

    <!-- My Wish Note -->
    @php($formOpen = $errors->addWish->any())
    <section class="card mt-8" x-data="{ open: {{ $formOpen ? 'true' : 'false' }}, adding: {{ $formOpen ? 'true' : 'false' }} }" aria-labelledby="my-wishes">
        <button type="button" @click="open = !open" :aria-expanded="open"
                class="flex w-full items-center justify-between gap-4 px-5 py-4 text-left focus-visible:outline-2 focus-visible:outline-pine">
            <span>
                <span id="my-wishes" class="font-hand text-3xl font-bold text-pine">Mein Wunschzettel</span>
                <span class="ml-1 text-sm text-ink-muted">{{ $myWishes->count() }} {{ $myWishes->count() === 1 ? 'Wunsch' : 'Wünsche' }}, öffentlich</span>
            </span>
            <svg class="size-5 shrink-0 text-ink-muted transition-transform" :class="{ 'rotate-180': open }" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div x-show="open" x-collapse x-cloak class="border-t border-line px-5 py-4">
            <div class="mb-4 flex flex-wrap gap-2">
                <button type="button" class="btn btn-primary" @click="adding = !adding" :aria-expanded="adding">
                    <svg class="size-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" aria-hidden="true"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                    Wunsch hinzufügen
                </button>
                <a href="{{ route('wishlist') }}" class="btn btn-ghost">Wunschzettel ansehen</a>
            </div>

            <div x-show="adding" x-collapse x-cloak class="mb-6 rounded-xl bg-paper p-5">
                <x-wish-form
                    :action="route('admin.wishes.store')"
                    variant="admin"
                    :show-receiver="false"
                    error-bag="addWish"
                    submit-text="Hinzufügen"
                    class="max-w-2xl"
                />
            </div>

            @if($myWishes->isEmpty())
                <p class="py-2 text-ink-muted">Noch keine Wünsche auf deinem Wunschzettel …</p>
            @else
                <ul class="divide-y divide-line">
                    @foreach($myWishes as $wish)
                        <li class="{{ $myRowGrid }} py-3">
                            <!-- Sort Nr -->
                            <form method="POST" action="{{ route('admin.wishes.update-sort', $wish) }}">
                                @csrf @method('PATCH')
                                <label class="flex items-center gap-1.5 text-xs font-bold text-ink-muted">
                                    <span class="sr-only">Sortierung</span>
                                    <input type="number" name="sortnr" value="{{ $wish->sortnr }}" min="0" step="1" required
                                           class="field-box w-16 px-2 py-1 text-center text-sm"
                                           onchange="this.form.requestSubmit()">
                                </label>
                            </form>

                            <a href="{{ route('wish.show', $wish) }}" tabindex="-1" aria-hidden="true">
                                <x-wish-thumb :wish="$wish" :tilt="$loop->odd ? '2deg' : '-3deg'" />
                            </a>

                            <div class="min-w-0">
                                <a href="{{ route('wish.show', $wish) }}" class="font-hand text-2xl font-bold leading-tight hover:text-pine">{{ $wish->title }}</a>
                                @if($wish->description)
                                    <p class="truncate text-sm text-ink-muted">{{ $wish->description }}</p>
                                @endif
                            </div>

                            <x-wish-actions :wish="$wish" />
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>
    </section>
</x-admin-layout>
