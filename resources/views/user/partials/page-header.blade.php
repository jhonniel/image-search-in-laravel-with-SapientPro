@php
    $eyebrow = $eyebrow ?? 'Your account';
    $description = $description ?? null;
@endphp
<div class="user-toolbar mb-1">
    <div class="min-w-0">
        @if(!empty($eyebrow))
            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-purple-600">{{ $eyebrow }}</p>
        @endif
        <h1 class="text-xl font-bold tracking-tight text-gray-900 sm:text-2xl md:text-3xl">{{ $title }}</h1>
        @if($description)
            <p class="mt-1 max-w-2xl text-sm text-gray-500">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex w-full flex-wrap shrink-0 items-center gap-2 sm:w-auto">{!! $actions !!}</div>
    @endisset
</div>
