@php
    $type = $type ?? 'success';
    $message = $message ?? '';
    $classes = match ($type) {
        'error' => 'user-alert-error',
        'warning' => 'flex items-start gap-3 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900',
        default => 'user-alert-success',
    };
    $icon = match ($type) {
        'error' => 'fa-exclamation-circle text-red-500',
        'warning' => 'fa-exclamation-triangle text-amber-500',
        default => 'fa-check-circle text-green-500 shrink-0 mt-0.5',
    };
@endphp
@if($message)
<div class="{{ $classes }}">
    <i class="fas {{ $icon }}"></i>
    <span>{{ $message }}</span>
</div>
@endif
