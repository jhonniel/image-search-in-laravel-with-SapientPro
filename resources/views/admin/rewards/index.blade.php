@extends('layouts.admin')

@section('title', 'Rewards Management - FindITFast Admin')

@section('content')
<div class="p-6">
    <div class="mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl sm:text-3xl font-bold text-gray-900">Rewards Management</h1>
                <p class="text-gray-600 mt-1 text-sm">Manage and send rewards to users</p>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="{{ route('rewards.create') }}" class="inline-flex items-center px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 transition-colors text-sm">
                    <i class="fas fa-plus mr-2"></i>
                    Create Reward
                </a>
                <a href="{{ route('rewards.send') }}" class="inline-flex items-center px-4 py-2 bg-pink-600 text-white rounded-lg hover:bg-pink-700 transition-colors text-sm">
                    <i class="fas fa-paper-plane mr-2"></i>
                    Send Reward
                </a>
                <form action="{{ route('rewards.auto-assign') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-colors text-sm">
                        <i class="fas fa-magic mr-2"></i>
                        Auto-Assign Rewards
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
    <div class="bg-green-50 border border-green-200 text-green-800 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-check-circle mr-2"></i>
            <span>{{ session('success') }}</span>
        </div>
    </div>
    @endif

    @if(session('error'))
    <div class="bg-red-50 border border-red-200 text-red-800 px-4 py-3 rounded-lg mb-6">
        <div class="flex items-center">
            <i class="fas fa-exclamation-circle mr-2"></i>
            <span>{{ session('error') }}</span>
        </div>
    </div>
    @endif

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-purple-100 text-purple-600">
                    <i class="fas fa-gift text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total Rewards</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-green-100 text-green-600">
                    <i class="fas fa-check-circle text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Active</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['active'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-blue-100 text-blue-600">
                    <i class="fas fa-check-double text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Used</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['used'] }}</p>
                </div>
            </div>
        </div>
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4">
            <div class="flex items-center">
                <div class="p-3 rounded-full bg-yellow-100 text-yellow-600">
                    <i class="fas fa-magic text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Auto-Assign</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['auto_assign'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Rewards Table -->
    <div class="bg-white rounded-lg shadow-sm border border-gray-200">
        <div class="px-6 py-4 border-b border-gray-200">
            <h3 class="text-lg font-semibold text-gray-900">All Rewards</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Title</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Type</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Code</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Rules</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse($rewards as $reward)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div>
                                <div class="text-sm font-medium text-gray-900">{{ $reward->title }}</div>
                                @if($reward->description)
                                <div class="text-xs text-gray-500 mt-1">{{ \Illuminate\Support\Str::limit($reward->description, 50) }}</div>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full
                                {{ $reward->type === 'discount' ? 'bg-blue-100 text-blue-800' : 
                                   ($reward->type === 'free_item' ? 'bg-green-100 text-green-800' : 'bg-purple-100 text-purple-800') }}">
                                {{ ucfirst(str_replace('_', ' ', $reward->type)) }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="font-mono text-sm text-gray-900">{{ $reward->code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($reward->user)
                            <div class="text-sm text-gray-900">{{ $reward->user->name }}</div>
                            <div class="text-xs text-gray-500">{{ $reward->user->email }}</div>
                            @else
                            <span class="text-sm text-gray-400 italic">Template</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($reward->is_used)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Used</span>
                            @elseif($reward->isExpired())
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800">Expired</span>
                            @else
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Active</span>
                            @endif
                            @if($reward->is_auto_assign)
                            <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-yellow-100 text-yellow-800 ml-1">Auto</span>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($reward->is_auto_assign)
                            <div class="text-xs text-gray-600">
                                @if($reward->min_reports)
                                <div>Min Reports: {{ $reward->min_reports }}</div>
                                @endif
                                @if($reward->min_claims)
                                <div>Min Claims: {{ $reward->min_claims }}</div>
                                @endif
                                @if($reward->min_found_items)
                                <div>Min Found: {{ $reward->min_found_items }}</div>
                                @endif
                                @if($reward->min_lost_items)
                                <div>Min Lost: {{ $reward->min_lost_items }}</div>
                                @endif
                            </div>
                            @else
                            <span class="text-xs text-gray-400">Manual</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                            @if($reward->user_id === null)
                            <form action="{{ route('rewards.destroy', $reward->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this reward?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-red-600 hover:text-red-900">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @else
                            <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-4 text-center text-sm text-gray-500">No rewards found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rewards->hasPages())
        <div class="px-6 py-4 border-t border-gray-200">
            {{ $rewards->links() }}
        </div>
        @endif
    </div>
</div>
@endsection

