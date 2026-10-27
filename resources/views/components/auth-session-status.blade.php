@props(['status'])

@if ($status)
    <div {{ $attributes->merge(['class' => 'font-bold text-sm text-pine']) }}>
        {{ $status }}
    </div>
@endif
