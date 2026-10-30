@props([
    'action',
    'method' => 'POST',
    'wish' => null,
    'submitText' => 'Absenden',
    'variant' => 'public',
    'showReceiver' => true,
    'showImage' => true,
    'showLinks' => true,
    'cancelUrl' => null,
    'errorBag' => 'default',
])

@php
    $isAdmin = $variant === 'admin';
    $field = $isAdmin ? 'field-box' : 'field-line';
    $label = $isAdmin ? 'block text-sm font-bold text-ink mb-1' : 'label-caps mb-1';
    $optional = '<span class="font-normal normal-case tracking-normal text-ink-muted">(optional)</span>';
    // Old input is what was sent, so a crafted request can bring lists where text belongs
    $oldText = fn (string $key, ?string $default) => is_array($value = old($key, $default)) ? '' : $value;
@endphp

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <!-- Receiver Field (conditional) -->
    @if($showReceiver)
        @php($selectedReceiver = old('receiver', $wish?->receiver ?? \App\Models\Wish::RECEIVERS[0]))
        {{-- Keep a stored receiver outside the list (e.g. an admin's name) selectable --}}
        @php($receivers = array_values(array_unique(array_filter([...\App\Models\Wish::RECEIVERS, $wish?->receiver]))))
        <fieldset>
            <legend class="{{ $label }}">{{ $isAdmin ? 'Name' : 'Von' }} *</legend>
            <div class="mt-2 flex flex-wrap gap-3">
                @foreach($receivers as $receiver)
                    @if($isAdmin)
                        <label class="pill-choice">
                            <input type="radio" name="receiver" value="{{ $receiver }}" class="sr-only" required @checked($selectedReceiver === $receiver)>
                            {{ $receiver }}
                        </label>
                    @else
                        <label class="stamp" style="--tilt: {{ $loop->even ? '2deg' : '-3deg' }}">
                            <input type="radio" name="receiver" value="{{ $receiver }}" class="sr-only" required @checked($selectedReceiver === $receiver)>
                            <span class="stamp-face">
                                <svg class="size-4 {{ $loop->even ? 'text-gold' : 'text-pine' }}" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    @if($loop->even)
                                        <path d="M12 2.5l2.9 6 6.6.9-4.8 4.6 1.2 6.5L12 17.4l-5.9 3.1 1.2-6.5L2.5 9.4l6.6-.9z"/>
                                    @else
                                        <path d="M12 2l4 6h-2l4 6h-2.5l3.5 5H13v3h-2v-3H5l3.5-5H6l4-6H8z"/>
                                    @endif
                                </svg>
                                <span class="stamp-name">{{ $receiver }}</span>
                            </span>
                        </label>
                    @endif
                @endforeach
            </div>
            @error('receiver', $errorBag)
            <p class="field-error">{{ $message }}</p>
            @enderror
        </fieldset>
    @endif

    <!-- Title Field -->
    <div>
        <label for="title" class="{{ $label }}">
            {{ $isAdmin ? 'Wunsch' : 'Ich wünsche mir' }} *
        </label>
        <input
            type="text"
            id="title"
            name="title"
            value="{{ $oldText('title', $wish?->title) }}"
            required
            class="{{ $field }}"
            placeholder="{{ $isAdmin ? 'Titel eingeben …' : 'eine Kuscheldecke, ein Buch, Kopfhörer …' }}">
        @error('title', $errorBag)
        <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <!-- Description Field -->
    <div>
        <label for="description" class="{{ $label }}">
            {{ $isAdmin ? 'Beschreibung' : 'Genauer gesagt' }} {!! $optional !!}
        </label>
        <textarea
            id="description"
            name="description"
            rows="3"
            maxlength="5000"
            class="{{ $field }} resize-none"
            placeholder="{{ $isAdmin ? 'Beschreibung eingeben …' : 'Farbe, Grösse, wo es das gibt …' }}">{{ $oldText('description', $wish?->description) }}</textarea>
        @error('description', $errorBag)
        <p class="field-error">{{ $message }}</p>
        @enderror
    </div>

    <!-- Image Upload (conditional) -->
    @if($showImage)
        <div x-data="{ preview: null, name: null }">
            <span class="{{ $label }}">Bild {!! $optional !!}</span>
            <label for="image" class="mt-2 flex cursor-pointer items-center gap-4 rounded-xl p-1 focus-within:outline-2 focus-within:outline-pine">
                <span class="polaroid shrink-0">
                    <span class="flex size-20 items-center justify-center overflow-hidden bg-pine-light {{ $isAdmin ? '' : 'lg:size-28' }}">
                        <template x-if="preview">
                            <img :src="preview" alt="" class="size-full object-cover">
                        </template>
                        @if($wish?->image)
                            <img x-show="!preview" src="{{ Storage::url($wish->image_thumbnail ?? $wish->image) }}" alt="{{ $wish->title }}" class="size-full object-cover">
                        @else
                            <svg x-show="!preview" class="size-7 text-pine" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4 8a2 2 0 012-2h1.5l1.5-2h6l1.5 2H18a2 2 0 012 2v9a2 2 0 01-2 2H6a2 2 0 01-2-2z"/>
                                <circle cx="12" cy="12.5" r="3.5"/>
                            </svg>
                        @endif
                    </span>
                </span>
                <span class="leading-tight">
                    <span class="block font-hand text-2xl text-ink" x-text="name ?? @js($wish?->image ? 'Bild ersetzen?' : 'Foto dazu?')">{{ $wish?->image ? 'Bild ersetzen?' : 'Foto dazu?' }}</span>
                    <span class="block text-sm text-ink-muted" x-text="name ? 'Tippen zum Ändern' : 'Tippen zum Anheften'">Tippen zum Anheften</span>
                </span>
                {{-- Only the types the request accepts; iPhones then send their HEIC photos as JPEG --}}
                <input
                    type="file"
                    id="image"
                    name="image"
                    accept="image/jpeg,image/png,image/gif"
                    class="sr-only"
                    @change="const file = $event.target.files[0]; if (preview) URL.revokeObjectURL(preview); preview = file ? URL.createObjectURL(file) : null; name = file?.name ?? null">
            </label>
            @error('image', $errorBag)
            <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <!-- Example Links (conditional) -->
    @if($showLinks)
        {{-- Old input is the text as typed; links of a stored wish are a list --}}
        @php($links = old('example_links', $wish?->example_links))
        <div>
            <label for="example_links" class="{{ $label }}">
                Links {!! $optional !!}
            </label>
            <textarea
                id="example_links"
                name="example_links"
                rows="2"
                class="{{ $field }} resize-none"
                placeholder="https://…">{{ is_array($links) ? implode("\n", array_filter($links, 'is_string')) : $links }}</textarea>
            <p class="mt-1 text-sm text-ink-muted">Ein Link pro Zeile oder kommagetrennt</p>
            {{-- Errors of single links come as example_links.0, example_links.1, … --}}
            @error('example_links*', $errorBag)
            <p class="field-error">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <!-- Actions -->
    @if($isAdmin)
        <div class="flex flex-wrap gap-3">
            <button type="submit" class="btn btn-primary">{{ $submitText }}</button>
            @if($cancelUrl)
                <a href="{{ $cancelUrl }}" class="btn btn-ghost">Abbrechen</a>
            @endif
        </div>
    @else
        <div class="flex justify-center pt-2">
            <button type="submit" class="seal">
                <span class="seal-disc">
                    <svg class="size-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 10h16v10H4zM3 7h18v3H3zM12 7v13M12 7c-2-3-6-3-6-1s4 1 6 1zm0 0c2-3 6-3 6-1s-4 1-6 1z"/>
                    </svg>
                </span>
                <span class="font-hand text-3xl font-bold text-seal">{{ $submitText }}</span>
            </button>
        </div>
    @endif
</form>
