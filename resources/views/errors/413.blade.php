{{-- PHP drops the whole request when it is bigger than post_max_size, so the typed wish is lost as well --}}
<x-public-layout :title="__('Too heavy for the sleigh').' · Wunschbox'">
    <x-slot:header>
        <h1 class="-rotate-2 font-hand text-6xl font-bold leading-none text-paper lg:text-7xl">{{ __('Too heavy for the sleigh') }}</h1>
        <p class="mt-2 text-pine-muted">{{ __('The picture is too large.') }}</p>
    </x-slot:header>

    <p class="text-lg leading-relaxed">
        {{ __('Please pick a smaller picture of at most :size MB and send your wish again. The text has to be typed again, sorry.', ['size' => \App\Http\Requests\StoreWishRequest::IMAGE_MAX_KILOBYTES / 1000]) }}
    </p>

    <p class="mt-8">
        <a href="{{ url()->previous(route('wishbox')) }}" class="font-bold text-pine underline underline-offset-4 hover:text-pine-dark">
            {{ __('Back') }}
        </a>
    </p>
</x-public-layout>
