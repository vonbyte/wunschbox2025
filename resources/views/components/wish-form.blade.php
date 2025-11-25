@props([
    'action',
    'method' => 'POST',
    'wish' => null,
    'submitText' => 'Absenden',
    'showReceiver' => true,
    'showImage' => true,
    'showLinks' => true,
    'cancelUrl' => null,
])

<form method="POST" action="{{ $action }}" enctype="multipart/form-data" {{ $attributes->merge(['class' => 'space-y-6']) }}>
    @csrf
    @if($method !== 'POST')
        @method($method)
    @endif

    <!-- Title Field -->
    <div>
        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">
            {{ $showReceiver ? 'Was wünschst du dir?' : 'Titel' }} *
        </label>
        <input
            type="text"
            id="title"
            name="title"
            value="{{ old('title', $wish?->title) }}"
            required
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition"
            placeholder="{{ $showReceiver ? 'z.B. Kuscheldecke, Buch, Kopfhörer...' : 'Titel eingeben...' }}">
        @error('title')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Receiver Field (conditional) -->
    @if($showReceiver)
        <div>
            <label for="receiver" class="block text-sm font-medium text-gray-700 mb-2">
                Dein Name *
            </label>
            <input
                type="text"
                id="receiver"
                name="receiver"
                value="{{ old('receiver', $wish?->receiver) }}"
                required
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition"
                placeholder="Wie heißt du?">
            @error('receiver')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <!-- Description Field -->
    <div>
        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">
            Beschreibung {{ $showReceiver ? '(optional)' : '' }}
        </label>
        <textarea
            id="description"
            name="description"
            rows="4"
            class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition resize-none"
            placeholder="{{ $showReceiver ? 'Erzähl uns mehr über deinen Wunsch...' : 'Beschreibung eingeben...' }}">{{ old('description', $wish?->description) }}</textarea>
        @error('description')
        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <!-- Image Upload (conditional) -->
    @if($showImage)
        <div>
            <label for="image" class="block text-sm font-medium text-gray-700 mb-2">
                Bild hochladen (optional)
            </label>
            @if($wish?->image)
                <div class="mb-3">
                    <img src="{{ Storage::url($wish->image) }}" alt="{{ $wish->title }}" class="w-32 h-32 object-cover rounded-lg">
                    <p class="text-sm text-gray-500 mt-1">Aktuelles Bild (wird ersetzt, wenn du ein neues hochlädst)</p>
                </div>
            @endif
            <input
                type="file"
                id="image"
                name="image"
                accept="image/*"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-christmas-red file:text-white hover:file:bg-red-700">
            @error('image')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <!-- Example Links (conditional) -->
    @if($showLinks)
        <div>
            <label for="example_links" class="block text-sm font-medium text-gray-700 mb-2">
                Beispiel-Links (optional)
            </label>
            <textarea
                id="example_links"
                name="example_links"
                rows="3"
                class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-christmas-red focus:border-transparent transition resize-none"
                placeholder="https://example.com/produkt1&#10;https://example.com/produkt2&#10;(Ein Link pro Zeile)">{{ old('example_links', $wish?->example_links ? implode("\n", $wish->example_links) : '') }}</textarea>
            <p class="mt-1 text-sm text-gray-500">Ein Link pro Zeile oder kommagetrennt</p>
            @error('example_links')
            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
            @enderror
        </div>
    @endif

    <!-- Actions -->
    @if($cancelUrl)
        <div class="flex gap-4">
            <a href="{{ $cancelUrl }}" class="flex-1 bg-gray-500 hover:bg-gray-600 text-white font-bold py-3 px-6 rounded-lg text-center transition">
                Abbrechen
            </a>
            <button
                type="submit"
                class="flex-1 bg-christmas-red hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg transition">
                {{ $submitText }}
            </button>
        </div>
    @else
        <button
            type="submit"
            class="w-full bg-christmas-red hover:bg-red-700 text-white font-bold py-4 px-6 rounded-lg transition duration-200 transform hover:scale-105 shadow-lg">
            {{ $submitText }}
        </button>
    @endif
</form>
