@php
    $eyebrow = $eyebrow ?? 'Administration';
    $description = $description ?? null;
@endphp
<div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
    <div>
        <p class="text-xs font-semibold uppercase tracking-wider text-purple-600 mb-1">{{ $eyebrow }}</p>
        <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 tracking-tight">{{ $title }}</h1>
        @if($description)
            <p class="text-sm text-gray-500 mt-1 max-w-2xl">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex flex-wrap shrink-0 items-center gap-2">{!! $actions !!}</div>
    @endisset
</div>
