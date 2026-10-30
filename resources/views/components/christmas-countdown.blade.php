{{-- "only" puts weight on it, like on the wishbox: "Nur noch 3 Tage …" instead of "noch 3 Tage …" --}}
@props(['only' => false])

@php
    $today = now(config('wishbox.timezone'))->startOfDay();
    $christmas = $today->copy()->setDate($today->year, 12, 24);
    if ($today->gt($christmas)) {
        $christmas->addYear();
    }
    $days = (int) $today->diffInDays($christmas);
@endphp

{{ match (true) {
    $days === 0 => __('Today is Christmas Eve'),
    $only => trans_choice('Only :count day until Christmas|Only :count days until Christmas', $days),
    default => trans_choice(':count day until Christmas|:count days until Christmas', $days),
} }}
