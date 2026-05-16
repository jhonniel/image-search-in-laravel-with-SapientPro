@extends('layouts.admin')

@section('title', 'Send Rewards - FindITFast Admin')

@section('content')
@php
    $sendBackAction = '<a href="'.route('rewards.index').'" class="admin-btn-secondary"><i class="fas fa-arrow-left text-xs"></i> Back to rewards</a>';
    $rewardOptions = $rewards->map(fn ($r) => [
        'id' => $r->id,
        'title' => $r->title,
        'type' => $r->type,
        'value' => $r->value,
        'description' => $r->description,
    ])->values();
@endphp
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Send reward',
        'description' => 'Pick a template and choose which users should receive a unique copy.',
        'actions' => $sendBackAction,
    ])

    @include('admin.partials.alert')

    @if($rewards->isEmpty())
    <div class="admin-card admin-card-body text-center py-12">
        <div class="w-14 h-14 rounded-2xl bg-amber-50 flex items-center justify-center mx-auto mb-4">
            <i class="fas fa-gift text-2xl text-amber-600"></i>
        </div>
        <p class="text-base font-semibold text-gray-900">No reward templates available</p>
        <p class="text-sm text-gray-500 mt-1 mb-4">Create a template first, then return here to send it.</p>
        <a href="{{ route('rewards.create') }}" class="admin-btn-primary inline-flex">
            <i class="fas fa-plus text-xs"></i> Create reward
        </a>
    </div>
    @else
    <form action="{{ route('rewards.send.post') }}" method="POST" class="space-y-6">
        @csrf

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <div class="lg:col-span-1 space-y-4">
                <div class="admin-card admin-card-body">
                    <label for="reward_id" class="block text-sm font-medium text-gray-700 mb-2">Reward template <span class="text-red-500">*</span></label>
                    <select id="reward_id" name="reward_id" required class="admin-select w-full">
                        <option value="">— Select template —</option>
                        @foreach($rewards as $reward)
                        <option value="{{ $reward->id }}" {{ (string) old('reward_id') === (string) $reward->id ? 'selected' : '' }}>
                            {{ $reward->title }}
                            @if($reward->value)
                                @if($reward->type === 'discount')
                                    ({{ $reward->value }}% off)
                                @else
                                    (${{ number_format($reward->value, 2) }})
                                @endif
                            @endif
                        </option>
                        @endforeach
                    </select>
                    @error('reward_id')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror

                    <div id="reward-preview" class="mt-4 hidden rounded-xl border border-purple-100 bg-purple-50/50 p-4 text-sm">
                        <p class="text-xs font-semibold uppercase tracking-wide text-purple-600 mb-2">Preview</p>
                        <p id="preview-title" class="font-semibold text-gray-900"></p>
                        <p id="preview-meta" class="text-purple-700 text-xs mt-1"></p>
                        <p id="preview-desc" class="text-gray-600 text-xs mt-2 line-clamp-3"></p>
                    </div>
                </div>
            </div>

            <div class="lg:col-span-2">
                <div class="admin-card overflow-hidden">
                    <div class="admin-toolbar flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h2 class="admin-panel-title">Select users</h2>
                            <p class="admin-panel-subtitle"><span id="selected-count">0</span> of {{ $users->count() }} selected</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <div class="relative">
                                <i class="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                <input type="text" id="user-search" placeholder="Search users…" class="admin-input pl-8 py-2 w-48 text-sm">
                            </div>
                            <button type="button" onclick="selectAll()" class="admin-btn-secondary !py-2 text-xs">Select all</button>
                            <button type="button" onclick="deselectAll()" class="admin-btn-secondary !py-2 text-xs">Clear</button>
                        </div>
                    </div>

                    <div class="p-4 sm:p-6 max-h-[28rem] overflow-y-auto bg-gray-50/50">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3" id="user-grid">
                            @foreach($users as $user)
                            @php $initials = strtoupper(substr(preg_replace('/\s+/', '', $user->name) ?: 'U', 0, 2)); @endphp
                            <label class="user-pick flex items-center gap-3 p-3 rounded-xl border border-gray-200 bg-white hover:border-purple-200 hover:bg-purple-50/30 cursor-pointer transition-colors"
                                   data-name="{{ strtolower($user->name) }}"
                                   data-email="{{ strtolower($user->email) }}">
                                <input type="checkbox" name="user_ids[]" value="{{ $user->id }}" class="user-checkbox rounded border-gray-300 text-purple-600 focus:ring-purple-500"
                                    {{ is_array(old('user_ids')) && in_array($user->id, old('user_ids')) ? 'checked' : '' }}>
                                <span class="h-9 w-9 rounded-full bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center text-[10px] font-semibold text-white shrink-0">{{ $initials }}</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block text-sm font-medium text-gray-900 truncate">{{ $user->name }}</span>
                                    <span class="block text-xs text-gray-500 truncate">{{ $user->email }}</span>
                                </span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @error('user_ids')<p class="text-red-600 text-xs px-6 pb-4">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('rewards.index') }}" class="admin-btn-secondary">Cancel</a>
            <button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-pink-600 rounded-lg shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition-colors">
                <i class="fas fa-paper-plane text-xs"></i>
                Send to selected users
            </button>
        </div>
    </form>
    @endif
</div>
@endsection

@push('scripts')
<script>
const rewardData = @json($rewardOptions);

function updateSelectedCount() {
    const checked = document.querySelectorAll('.user-checkbox:checked').length;
    const el = document.getElementById('selected-count');
    if (el) el.textContent = String(checked);
}

function selectAll() {
    document.querySelectorAll('.user-pick:not(.hidden) .user-checkbox').forEach(cb => { cb.checked = true; });
    updateSelectedCount();
}

function deselectAll() {
    document.querySelectorAll('.user-checkbox').forEach(cb => { cb.checked = false; });
    updateSelectedCount();
}

function updatePreview() {
    const id = document.getElementById('reward_id')?.value;
    const box = document.getElementById('reward-preview');
    if (!box) return;
    const reward = rewardData.find(r => String(r.id) === String(id));
    if (!reward) {
        box.classList.add('hidden');
        return;
    }
    box.classList.remove('hidden');
    document.getElementById('preview-title').textContent = reward.title;
    let meta = reward.type.replace('_', ' ');
    if (reward.value) {
        meta += reward.type === 'discount' ? ` · ${reward.value}% off` : ` · $${Number(reward.value).toFixed(2)}`;
    }
    document.getElementById('preview-meta').textContent = meta;
    document.getElementById('preview-desc').textContent = reward.description || 'No description';
}

document.getElementById('reward_id')?.addEventListener('change', updatePreview);

document.getElementById('user-search')?.addEventListener('input', function (e) {
    const term = e.target.value.trim().toLowerCase();
    document.querySelectorAll('.user-pick').forEach(row => {
        const name = row.dataset.name || '';
        const email = row.dataset.email || '';
        row.classList.toggle('hidden', term && !name.includes(term) && !email.includes(term));
    });
});

document.querySelectorAll('.user-checkbox').forEach(cb => {
    cb.addEventListener('change', updateSelectedCount);
});

updatePreview();
updateSelectedCount();
</script>
@endpush
