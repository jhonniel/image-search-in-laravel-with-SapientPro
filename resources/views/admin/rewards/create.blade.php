@extends('layouts.admin')

@section('title', 'Create Reward - FindITFast Admin')

@section('content')
@php
    $createBackAction = '<a href="'.route('rewards.index').'" class="admin-btn-secondary"><i class="fas fa-arrow-left text-xs"></i> Back to rewards</a>';
@endphp
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Create reward',
        'description' => 'Define a reward template you can send manually or auto-assign when users hit activity goals.',
        'actions' => $createBackAction,
    ])

    @include('admin.partials.alert')

    <form action="{{ route('rewards.store') }}" method="POST" class="space-y-6 max-w-4xl">
        @csrf

        <div class="admin-card admin-card-body">
            <h2 class="admin-panel-title mb-1">Basic information</h2>
            <p class="admin-panel-subtitle mb-6">Title, type, value, and optional expiry for this reward.</p>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-700 mb-1">Title <span class="text-red-500">*</span></label>
                    <input type="text" id="title" name="title" value="{{ old('title') }}" required class="admin-input px-4 py-2.5" placeholder="e.g. 10% off next report">
                    @error('title')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="type" class="block text-sm font-medium text-gray-700 mb-1">Type <span class="text-red-500">*</span></label>
                    <select id="type" name="type" required class="admin-select w-full">
                        <option value="discount" {{ old('type') === 'discount' ? 'selected' : '' }}>Discount</option>
                        <option value="free_item" {{ old('type', '') === 'free_item' ? 'selected' : '' }}>Free item</option>
                        <option value="cashback" {{ old('type') === 'cashback' ? 'selected' : '' }}>Cashback</option>
                    </select>
                    @error('type')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="value" class="block text-sm font-medium text-gray-700 mb-1">Value</label>
                    <input type="number" id="value" name="value" value="{{ old('value') }}" step="0.01" min="0" class="admin-input px-4 py-2.5" placeholder="10 or 25.00">
                    <p class="text-xs text-gray-500 mt-1">Discount = percent. Cashback = dollar amount.</p>
                    @error('value')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">Promo code</label>
                    <input type="text" id="code" name="code" value="{{ old('code') }}" class="admin-input px-4 py-2.5 font-mono" placeholder="Auto-generated if empty">
                    @error('code')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="description" class="block text-sm font-medium text-gray-700 mb-1">Description</label>
                    <textarea id="description" name="description" rows="3" class="admin-input px-4 py-2.5 resize-y" placeholder="What users see when they receive this reward">{{ old('description') }}</textarea>
                    @error('description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-1">Expires on</label>
                    <input type="date" id="expires_at" name="expires_at" value="{{ old('expires_at') }}" class="admin-input px-4 py-2.5">
                    @error('expires_at')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="admin-card admin-card-body">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="admin-panel-title">Auto-assign rules</h2>
                    <p class="admin-panel-subtitle">Automatically grant this reward when a user meets all set thresholds.</p>
                </div>
                <label class="relative inline-flex items-center cursor-pointer shrink-0">
                    <input type="checkbox" id="is_auto_assign" name="is_auto_assign" value="1" {{ old('is_auto_assign') ? 'checked' : '' }} class="sr-only peer" onchange="toggleRules(this)">
                    <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300/40 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-600"></div>
                    <span class="ml-3 text-sm font-medium text-gray-700">Enable auto-assign</span>
                </label>
            </div>

            <div id="rules-section" class="grid grid-cols-1 md:grid-cols-2 gap-4 {{ old('is_auto_assign') ? '' : 'hidden' }}">
                <div>
                    <label for="min_reports" class="block text-sm font-medium text-gray-700 mb-1">Minimum reports</label>
                    <input type="number" id="min_reports" name="min_reports" value="{{ old('min_reports') }}" min="0" class="admin-input px-4 py-2.5">
                    @error('min_reports')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="min_claims" class="block text-sm font-medium text-gray-700 mb-1">Minimum claims</label>
                    <input type="number" id="min_claims" name="min_claims" value="{{ old('min_claims') }}" min="0" class="admin-input px-4 py-2.5">
                    @error('min_claims')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="min_found_items" class="block text-sm font-medium text-gray-700 mb-1">Minimum found items</label>
                    <input type="number" id="min_found_items" name="min_found_items" value="{{ old('min_found_items') }}" min="0" class="admin-input px-4 py-2.5">
                    @error('min_found_items')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="min_lost_items" class="block text-sm font-medium text-gray-700 mb-1">Minimum lost items</label>
                    <input type="number" id="min_lost_items" name="min_lost_items" value="{{ old('min_lost_items') }}" min="0" class="admin-input px-4 py-2.5">
                    @error('min_lost_items')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
                <div class="md:col-span-2">
                    <label for="rule_description" class="block text-sm font-medium text-gray-700 mb-1">Rule description (shown to users)</label>
                    <textarea id="rule_description" name="rule_description" rows="2" class="admin-input px-4 py-2.5 resize-y" placeholder="Earn this when you report 5 items">{{ old('rule_description') }}</textarea>
                    @error('rule_description')<p class="text-red-600 text-xs mt-1">{{ $message }}</p>@enderror
                </div>
            </div>
        </div>

        <div class="flex flex-wrap items-center justify-end gap-3">
            <a href="{{ route('rewards.index') }}" class="admin-btn-secondary">Cancel</a>
            <button type="submit" class="admin-btn-primary">
                <i class="fas fa-save text-xs"></i>
                Create reward
            </button>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function toggleRules(checkbox) {
    const section = document.getElementById('rules-section');
    section.classList.toggle('hidden', !checkbox.checked);
}
</script>
@endpush
