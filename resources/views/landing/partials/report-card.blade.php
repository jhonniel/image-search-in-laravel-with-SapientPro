@php
    $showImage = !empty($report['image_path']);
    $typeClass = ($report['type'] ?? '') === 'lost' ? 'landing-badge-lost' : 'landing-badge-found';
@endphp
<div class="landing-card group flex h-full flex-col">
    @if($showImage)
    <div class="relative h-44 shrink-0 overflow-hidden bg-gray-100 sm:h-48">
        <img src="{{ $report['image_path'] }}"
             alt="{{ \Illuminate\Support\Str::limit($report['title'], 50) }}"
             class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105"
             onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'400\' height=\'300\'%3E%3Crect fill=\'%23f1f5f9\' width=\'400\' height=\'300\'/%3E%3Ctext fill=\'%2394a3b8\' font-family=\'sans-serif\' font-size=\'18\' x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\'%3ENo image%3C/text%3E%3C/svg%3E';">
        <div class="absolute inset-0 bg-gradient-to-t from-gray-900/40 via-transparent to-transparent opacity-0 transition-opacity group-hover:opacity-100"></div>
        <span class="absolute left-3 top-3 {{ $typeClass }} shadow-sm">{{ ucfirst($report['type']) }}</span>
    </div>
    @endif

    <div class="flex flex-1 flex-col p-5 sm:p-6">
        <div class="mb-3 flex items-start justify-between gap-2">
            <h3 class="line-clamp-2 flex-1 text-base font-bold leading-snug text-gray-900">{{ \Illuminate\Support\Str::limit($report['title'], 50) }}</h3>
            @unless($showImage)
            <span class="{{ $typeClass }} shrink-0">{{ ucfirst($report['type']) }}</span>
            @endunless
        </div>

        <div class="mt-auto space-y-3">
            <div class="flex items-start gap-2 rounded-xl bg-purple-50/80 px-3 py-2.5 ring-1 ring-purple-100/80">
                <i class="fas fa-map-marker-alt mt-0.5 shrink-0 text-sm text-purple-600"></i>
                <span class="line-clamp-2 text-sm font-medium text-gray-700">{{ \Illuminate\Support\Str::limit($report['location'], 40) }}</span>
            </div>

            @if(!empty($report['detected_objects']) && is_array($report['detected_objects']))
            @php
                $displayedObjects = array_slice($report['detected_objects'], 0, 3);
                $displayedCount = count(array_filter($displayedObjects, function ($obj) {
                    $name = is_array($obj) && isset($obj['name']) ? $obj['name'] : (is_string($obj) ? $obj : '');
                    return !empty($name);
                }));
            @endphp
            @if($displayedCount > 0)
            <div>
                <h4 class="mb-1.5 flex items-center text-xs font-semibold text-gray-700">
                    <i class="fas fa-cube mr-1 text-blue-600"></i>
                    Detected Objects ({{ $displayedCount }}):
                </h4>
                <div class="flex flex-wrap gap-1.5">
                    @foreach($displayedObjects as $obj)
                        @php
                            $objName = is_array($obj) && isset($obj['name']) ? $obj['name'] : (is_string($obj) ? $obj : '');
                            $objScore = is_array($obj) && isset($obj['score']) ? $obj['score'] : 0.0;
                            $confidencePercent = round($objScore * 100);
                        @endphp
                        @if(!empty($objName))
                        <span class="inline-flex items-center rounded-full bg-blue-50 px-2 py-0.5 text-xs font-medium text-blue-800 ring-1 ring-blue-100"
                              title="Detected from image{{ $confidencePercent ? ' (' . $confidencePercent . '% confidence)' : '' }}">
                            <i class="fas fa-eye mr-1 text-[10px]"></i>{{ $objName }}
                        </span>
                        @endif
                    @endforeach
                </div>
            </div>
            @endif
            @endif

            <div class="flex items-center border-t border-gray-100 pt-3 text-xs text-gray-500">
                <i class="fas fa-clock mr-1.5 text-purple-500"></i>
                <span class="font-medium">{{ $report['time_ago'] }}</span>
            </div>
        </div>
    </div>
</div>
