@extends('layouts.admin')

@section('title', 'Send Rewards - FindITFast Admin')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Send Rewards</h1>
                <p class="text-gray-600 mt-1 text-sm">Send rewards to selected users</p>
            </div>
            <a href="{{ route('rewards.index') }}" class="px-4 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-sm">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Rewards
            </a>
        </div>
    </div>

    <div class="max-w-6xl">
        <form action="{{ route('rewards.send.post') }}" method="POST" class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
            @csrf

            <!-- Select Reward -->
            <div class="mb-6">
                <label for="reward_id" class="block text-sm font-medium text-gray-700 mb-2">Select Reward Template *</label>
                <select id="reward_id" name="reward_id" required
                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    <option value="">-- Select a reward --</option>
                    @foreach($rewards as $reward)
                    <option value="{{ $reward->id }}">
                        {{ $reward->title }} 
                        @if($reward->value)
                        - 
                        @if($reward->type === 'discount')
                            {{ $reward->value }}% OFF
                        @else
                            ${{ number_format($reward->value, 2) }}
                        @endif
                        @endif
                    </option>
                    @endforeach
                </select>
                @error('reward_id')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Select Users -->
            <div class="mb-6">
                <div class="flex items-center justify-between mb-4">
                    <label class="block text-sm font-medium text-gray-700">Select Users *</label>
                    <div class="flex gap-2">
                        <button type="button" onclick="selectAll()" class="text-xs text-purple-600 hover:text-purple-800">Select All</button>
                        <button type="button" onclick="deselectAll()" class="text-xs text-gray-600 hover:text-gray-800">Deselect All</button>
                    </div>
                </div>
                
                <div class="border border-gray-300 rounded-lg p-4 max-h-96 overflow-y-auto">
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
                        @foreach($users as $user)
                        <label class="flex items-center p-3 border border-gray-200 rounded-lg hover:bg-gray-50 cursor-pointer">
                            <input type="checkbox" name="user_ids[]" value="{{ $user->id }}"
                                   class="rounded border-gray-300 text-purple-600 focus:ring-purple-500">
                            <div class="ml-3 flex-1 min-w-0">
                                <div class="text-sm font-medium text-gray-900 truncate">{{ $user->name }}</div>
                                <div class="text-xs text-gray-500 truncate">{{ $user->email }}</div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>
                @error('user_ids')
                <p class="text-red-600 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Submit Button -->
            <div class="flex items-center justify-end space-x-4 pt-4 border-t">
                <a href="{{ route('rewards.index') }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Rewards
                </button>
            </div>
        </form>
    </div>
</div>

<script>
function selectAll() {
    document.querySelectorAll('input[name="user_ids[]"]').forEach(checkbox => {
        checkbox.checked = true;
    });
}

function deselectAll() {
    document.querySelectorAll('input[name="user_ids[]"]').forEach(checkbox => {
        checkbox.checked = false;
    });
}
</script>
@endsection

