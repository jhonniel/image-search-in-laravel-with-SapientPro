@if(session('success'))
<div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
    <i class="fas fa-check-circle mt-0.5 text-emerald-600"></i>
    <span>{{ session('success') }}</span>
</div>
@endif
@if(session('error'))
<div class="flex items-start gap-3 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
    <i class="fas fa-exclamation-circle mt-0.5 text-red-600"></i>
    <span>{{ session('error') }}</span>
</div>
@endif
@if($errors ?? false)
    @if($errors->any())
    <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
        <ul class="list-disc list-inside space-y-1">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
@endif
