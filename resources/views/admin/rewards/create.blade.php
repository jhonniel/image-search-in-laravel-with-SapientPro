@extends('layouts.admin')

@section('title', 'Create Reward - FindITFast Admin')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Create Reward</h1>
                <p class="text-gray-600 mt-1 text-sm">Create a new reward template with rules</p>
            </div>
            <a href="{{ route('admin.rewards.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Rewards
            </a>
        </div>
    </div>

    <div class="max-w-4xl">
        <form action="{{ route('admin.rewards.store') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @csrf

            <!-- Basic Information -->
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Basic Information</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label for="title" class="block text-sm font-medium text-gray-700 mb-2">Title *</label>
                        <input type="text" id="title" name="title" value="{{ old('title') }}" required
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('title')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="type" class="block text-sm font-medium text-gray-700 mb-2">Type *</label>
                        <select id="type" name="type" required
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="discount" {{ old('type') === 'discount' ? 'selected' : '' }}>Discount</option>
                            <option value="free_item" {{ old('type') === 'free_item' ? 'selected' : '' }}>Free Item</option>
                            <option value="cashback" {{ old('type') === 'cashback' ? 'selected' : '' }}>Cashback</option>
                        </select>
                        @error('type')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="value" class="block text-sm font-medium text-gray-700 mb-2">Value</label>
                        <input type="number" id="value" name="value" value="{{ old('value') }}" step="0.01" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="e.g., 10 for 10% or $10">
                        <p class="text-xs text-gray-500 mt-1">For discount: percentage (e.g., 10 = 10%). For cashback: amount</p>
                        @error('value')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="code" class="block text-sm font-medium text-gray-700 mb-2">Code</label>
                        <input type="text" id="code" name="code" value="{{ old('code') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="Leave empty to auto-generate">
                        @error('code')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="description" class="block text-sm font-medium text-gray-700 mb-2">Description</label>
                        <textarea id="description" name="description" rows="3"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">{{ old('description') }}</textarea>
                        @error('description')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="expires_at" class="block text-sm font-medium text-gray-700 mb-2">Expires At</label>
                        <input type="date" id="expires_at" name="expires_at" value="{{ old('expires_at') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                        @error('expires_at')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Reward Rules -->
            <div class="mb-6 border-t pt-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-semibold text-gray-900">Reward Rules (Auto-Assign)</h3>
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" id="is_auto_assign" name="is_auto_assign" value="1" {{ old('is_auto_assign') ? 'checked' : '' }}
                               class="sr-only peer" onchange="toggleRules(this)">
                        <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none peer-focus:ring-4 peer-focus:ring-purple-300 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-purple-primary"></div>
                        <span class="ml-3 text-sm text-gray-700">Enable Auto-Assign</span>
                    </label>
                </div>
                <p class="text-sm text-gray-600 mb-4">Set criteria for users to automatically earn this reward</p>
                
                <div id="rules-section" class="grid grid-cols-1 md:grid-cols-2 gap-4" style="display: {{ old('is_auto_assign') ? 'grid' : 'none' }};">
                    <div>
                        <label for="min_reports" class="block text-sm font-medium text-gray-700 mb-2">Minimum Reports</label>
                        <input type="number" id="min_reports" name="min_reports" value="{{ old('min_reports') }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="e.g., 5">
                        @error('min_reports')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="min_claims" class="block text-sm font-medium text-gray-700 mb-2">Minimum Claims</label>
                        <input type="number" id="min_claims" name="min_claims" value="{{ old('min_claims') }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="e.g., 3">
                        @error('min_claims')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="min_found_items" class="block text-sm font-medium text-gray-700 mb-2">Minimum Found Items</label>
                        <input type="number" id="min_found_items" name="min_found_items" value="{{ old('min_found_items') }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="e.g., 2">
                        @error('min_found_items')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="min_lost_items" class="block text-sm font-medium text-gray-700 mb-2">Minimum Lost Items</label>
                        <input type="number" id="min_lost_items" name="min_lost_items" value="{{ old('min_lost_items') }}" min="0"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                               placeholder="e.g., 3">
                        @error('min_lost_items')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label for="rule_description" class="block text-sm font-medium text-gray-700 mb-2">Rule Description</label>
                        <textarea id="rule_description" name="rule_description" rows="2"
                                  class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500"
                                  placeholder="e.g., Earn this reward when you report 5 items">{{ old('rule_description') }}</textarea>
                        @error('rule_description')
                        <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="{{ route('admin.rewards.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Create Reward
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleRules(checkbox) {
    const rulesSection = document.getElementById('rules-section');
    if (checkbox.checked) {
        rulesSection.style.display = 'grid';
    } else {
        rulesSection.style.display = 'none';
    }
}
</script>
@endsection

