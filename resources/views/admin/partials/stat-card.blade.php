@php
    $iconBg = $iconBg ?? 'bg-purple-100';
    $iconColor = $iconColor ?? 'text-purple-600';
    $subtext = $subtext ?? null;
@endphp
<div class="bg-white rounded-xl border border-gray-200 shadow-sm p-5 flex items-center gap-4 h-full">
    <div class="w-11 h-11 rounded-xl {{ $iconBg }} flex items-center justify-center shrink-0">
        <i class="fas {{ $icon }} {{ $iconColor }}"></i>
    </div>
    <div class="min-w-0">
        <p class="text-sm text-gray-500">{{ $label }}</p>
        <p class="text-2xl font-bold text-gray-900 tabular-nums">{{ $value }}</p>
        @if($subtext)
            <p class="text-xs text-gray-500 mt-0.5">{{ $subtext }}</p>
        @endif
    </div>
</div>
