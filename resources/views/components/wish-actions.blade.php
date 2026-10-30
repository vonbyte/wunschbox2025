@props(['wish'])

<div {{ $attributes->merge(['class' => 'flex items-center justify-end gap-1']) }}>
    <a href="{{ route('admin.wishes.edit', $wish) }}" class="icon-btn" title="{{ __('Edit') }}" aria-label="{{ __('Edit :title', ['title' => $wish->title]) }}">
        <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M4 20h4L19 9l-4-4L4 16zM13.5 6.5l4 4"/>
        </svg>
    </a>

    {{-- Only the owner's wishes can go on the public wish note --}}
    @if($wish->canTogglePublic())
    <form method="POST" action="{{ route('admin.wishes.toggle', $wish) }}">
        @csrf @method('PATCH')
        @if($wish->is_public)
            <button type="submit" class="icon-btn" title="{{ __('Make private') }}" aria-label="{{ __('Make :title private', ['title' => $wish->title]) }}">
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 3l18 18M10.6 10.6a2 2 0 002.8 2.8M9.9 5.1A9.8 9.8 0 0112 5c4.5 0 8.3 2.9 9.5 7a10 10 0 01-2.9 4.4M6.6 6.6A10 10 0 002.5 12c1.2 4.1 5 7 9.5 7 1.6 0 3.1-.4 4.4-1"/>
                </svg>
            </button>
        @else
            <button type="submit" class="icon-btn hover:text-gold" title="{{ __('Put on my wish note') }}" aria-label="{{ __('Put :title on my wish note', ['title' => $wish->title]) }}">
                <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                    <path stroke-linejoin="round" d="M12 3.5l2.6 5.3 5.9.9-4.3 4.1 1 5.8L12 16.9l-5.2 2.7 1-5.8-4.3-4.1 5.9-.9z"/>
                </svg>
            </button>
        @endif
    </form>
    @endif

    <form method="POST" action="{{ route('admin.wishes.destroy', $wish) }}" onsubmit="return confirm(@js(__('Really delete?')))">
        @csrf @method('DELETE')
        <button type="submit" class="icon-btn text-seal hover:bg-berry-light hover:text-seal" title="{{ __('Delete') }}" aria-label="{{ __('Delete :title', ['title' => $wish->title]) }}">
            <svg class="size-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16M10 11v6M14 11v6M5 7l1 12a2 2 0 002 2h8a2 2 0 002-2l1-12M9 7V4h6v3"/>
            </svg>
        </button>
    </form>
</div>
