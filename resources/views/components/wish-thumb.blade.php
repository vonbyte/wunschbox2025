@props(['wish', 'tilt' => '-3deg', 'size' => 'size-10'])

<span {{ $attributes->merge(['class' => 'polaroid p-0.5 pb-2']) }} style="--tilt: {{ $tilt }}">
    @if($wish->image)
        <img src="{{ Storage::url($wish->image_thumbnail ?? $wish->image) }}" alt="" class="{{ $size }} object-cover" loading="lazy">
    @else
        <span class="flex {{ $size }} items-center justify-center bg-pine-light text-pine">
            <svg class="size-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 10h16v10H4zM3 7h18v3H3zM12 7v13M12 7c-2-3-6-3-6-1s4 1 6 1zm0 0c2-3 6-3 6-1s-4 1-6 1z"/>
            </svg>
        </span>
    @endif
</span>
