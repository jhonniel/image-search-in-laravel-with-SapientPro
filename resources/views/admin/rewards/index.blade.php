@extends('layouts.admin')

@section('title', 'Rewards Management - FindITFast Admin')

@section('content')
@php
    $rewardsHeaderActions = '<a href="'.route('rewards.create').'" class="admin-btn-primary"><i class="fas fa-plus text-xs"></i> Create reward</a>'
        .'<a href="'.route('rewards.send').'" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-pink-600 rounded-lg shadow-sm hover:bg-pink-700 focus:outline-none focus:ring-2 focus:ring-pink-500 focus:ring-offset-2 transition-colors"><i class="fas fa-paper-plane text-xs"></i> Send reward</a>'
        .'<form action="'.route('rewards.auto-assign').'" method="POST" class="inline" onsubmit="return confirm(\'Run auto-assign for all eligible users?\');">'
        .'<input type="hidden" name="_token" value="'.csrf_token().'">'
        .'<button type="submit" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 text-sm font-medium text-white bg-emerald-600 rounded-lg shadow-sm hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 transition-colors"><i class="fas fa-wand-magic-sparkles text-xs"></i> Auto-assign</button>'
        .'</form>';
@endphp
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Rewards Management',
        'description' => 'Create reward templates, send them to users, or auto-assign when they meet activity rules.',
        'actions' => $rewardsHeaderActions,
    ])

    @include('admin.partials.alert')

    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
        @include('admin.partials.stat-card', ['label' => 'Total rewards', 'value' => number_format($stats['total']), 'icon' => 'fa-gift', 'iconBg' => 'bg-purple-100', 'iconColor' => 'text-purple-600'])
        @include('admin.partials.stat-card', ['label' => 'Templates', 'value' => number_format($stats['templates']), 'icon' => 'fa-layer-group', 'iconBg' => 'bg-indigo-100', 'iconColor' => 'text-indigo-600', 'subtext' => 'Ready to send'])
        @include('admin.partials.stat-card', ['label' => 'Assigned', 'value' => number_format($stats['assigned']), 'icon' => 'fa-user-check', 'iconBg' => 'bg-blue-100', 'iconColor' => 'text-blue-600'])
        @include('admin.partials.stat-card', ['label' => 'Auto-assign rules', 'value' => number_format($stats['auto_assign']), 'icon' => 'fa-wand-magic-sparkles', 'iconBg' => 'bg-amber-100', 'iconColor' => 'text-amber-600'])
    </div>

    <div class="admin-card">
        <div class="admin-toolbar flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
            <div>
                <h2 class="admin-panel-title">Rewards list</h2>
                <p class="admin-panel-subtitle">{{ $rewards->total() }} record{{ $rewards->total() === 1 ? '' : 's' }} in this view</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                @foreach([
                    'all' => 'All',
                    'templates' => 'Templates',
                    'assigned' => 'Assigned',
                    'active' => 'Active',
                    'used' => 'Used',
                ] as $key => $label)
                    <a href="{{ route('rewards.index', ['filter' => $key]) }}"
                       class="inline-flex items-center px-3 py-1.5 rounded-lg text-sm font-medium transition-colors {{ ($filter ?? 'all') === $key ? 'bg-purple-600 text-white shadow-sm' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50' }}">
                        {{ $label }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead>
                    <tr class="border-b border-gray-200 bg-white">
                        <th class="admin-th">Reward</th>
                        <th class="admin-th">Type</th>
                        <th class="admin-th">Code</th>
                        <th class="admin-th hidden md:table-cell">Recipient</th>
                        <th class="admin-th">Status</th>
                        <th class="admin-th hidden lg:table-cell">Rules</th>
                        <th class="admin-th text-right w-20">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rewards as $reward)
                    @php
                        $typeClass = match ($reward->type) {
                            'discount' => 'bg-blue-50 text-blue-700 ring-blue-600/10',
                            'free_item' => 'bg-emerald-50 text-emerald-700 ring-emerald-600/10',
                            default => 'bg-purple-50 text-purple-700 ring-purple-600/10',
                        };
                    @endphp
                    <tr class="admin-table-row">
                        <td class="admin-td">
                            <div class="flex items-start gap-3 min-w-[200px]">
                                <div class="h-10 w-10 rounded-xl bg-gradient-to-br from-purple-500 to-pink-500 flex items-center justify-center shrink-0 text-white">
                                    <i class="fas fa-gift text-sm"></i>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm font-semibold text-gray-900">{{ $reward->title }}</p>
                                    @if($reward->description)
                                    <p class="text-xs text-gray-500 mt-0.5 line-clamp-2">{{ $reward->description }}</p>
                                    @endif
                                    @if($reward->value)
                                    <p class="text-xs font-medium text-purple-600 mt-1">
                                        @if($reward->type === 'discount')
                                            {{ $reward->value }}% off
                                        @else
                                            ${{ number_format($reward->value, 2) }}
                                        @endif
                                    </p>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium ring-1 {{ $typeClass }}">
                                {{ ucfirst(str_replace('_', ' ', $reward->type)) }}
                            </span>
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <code class="text-xs font-mono bg-gray-100 text-gray-800 px-2 py-1 rounded-md">{{ $reward->code }}</code>
                        </td>
                        <td class="admin-td hidden md:table-cell">
                            @if($reward->user)
                            <p class="text-sm font-medium text-gray-900">{{ $reward->user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $reward->user->email }}</p>
                            @else
                            <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-indigo-50 text-indigo-700 ring-1 ring-indigo-600/10">Template</span>
                            @endif
                        </td>
                        <td class="admin-td whitespace-nowrap">
                            <div class="flex flex-wrap gap-1">
                                @if($reward->is_used)
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-gray-100 text-gray-700 ring-1 ring-gray-500/10">Used</span>
                                @elseif($reward->isExpired())
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-red-50 text-red-700 ring-1 ring-red-600/10">Expired</span>
                                @else
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/10">Active</span>
                                @endif
                                @if($reward->is_auto_assign)
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-amber-50 text-amber-700 ring-1 ring-amber-600/10">Auto</span>
                                @endif
                            </div>
                        </td>
                        <td class="admin-td hidden lg:table-cell text-xs text-gray-600">
                            @if($reward->is_auto_assign)
                            <ul class="space-y-0.5">
                                @if($reward->min_reports)<li>Reports ≥ {{ $reward->min_reports }}</li>@endif
                                @if($reward->min_claims)<li>Claims ≥ {{ $reward->min_claims }}</li>@endif
                                @if($reward->min_found_items)<li>Found ≥ {{ $reward->min_found_items }}</li>@endif
                                @if($reward->min_lost_items)<li>Lost ≥ {{ $reward->min_lost_items }}</li>@endif
                            </ul>
                            @else
                            <span class="text-gray-400">Manual send</span>
                            @endif
                        </td>
                        <td class="admin-td text-right">
                            @if($reward->user_id === null)
                            <form action="{{ route('rewards.destroy', $reward->id) }}" method="POST" class="inline" onsubmit="return confirm('Delete this reward template?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="inline-flex h-8 w-8 items-center justify-center rounded-lg text-gray-500 hover:bg-red-50 hover:text-red-600 transition-colors" title="Delete template">
                                    <i class="fas fa-trash text-sm"></i>
                                </button>
                            </form>
                            @else
                            <span class="text-gray-300 text-sm">—</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-16 text-center">
                            <div class="w-14 h-14 rounded-2xl bg-purple-50 flex items-center justify-center mx-auto mb-4">
                                <i class="fas fa-gift text-2xl text-purple-600"></i>
                            </div>
                            <p class="text-base font-semibold text-gray-900">No rewards found</p>
                            <p class="text-sm text-gray-500 mt-1 max-w-sm mx-auto mb-4">
                                @if(($filter ?? 'all') === 'all')
                                    Create a template, then send it to users or enable auto-assign rules.
                                @else
                                    Nothing matches this filter. Try another tab or create a new reward.
                                @endif
                            </p>
                            <a href="{{ route('rewards.create') }}" class="admin-btn-primary inline-flex">
                                <i class="fas fa-plus text-xs"></i> Create reward
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($rewards->hasPages())
        <div class="px-4 sm:px-6 py-4 border-t border-gray-100 bg-gray-50/50">
            {{ $rewards->links() }}
        </div>
        @endif
    </div>
</div>
@endsection
