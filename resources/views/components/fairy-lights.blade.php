@props(['size' => 'md'])

{{-- Static SVG from resources/images, rebuild it with `php artisan wishbox:fairy-lights` --}}
<div aria-hidden="true" {{ $attributes->merge(['class' => $size === 'sm' ? 'fairy-lights fairy-lights-sm' : 'fairy-lights']) }}></div>
