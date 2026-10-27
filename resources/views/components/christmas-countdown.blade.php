@props(['lead' => 'noch'])

@php
    $today = now()->startOfDay();
    $christmas = $today->copy()->setDate($today->year, 12, 24);
    if ($today->gt($christmas)) {
        $christmas->addYear();
    }
    $days = (int) $today->diffInDays($christmas);
@endphp

{{ $days === 0 ? 'Heute ist Heiligabend' : "{$lead} {$days} ".($days === 1 ? 'Tag' : 'Tage').' bis Weihnachten' }}
