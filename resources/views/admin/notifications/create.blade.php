@extends('layouts.admin')

@section('title', 'Send Notifications - FindITFast Admin')

@section('content')
<div class="admin-page">
    @include('admin.partials.page-header', [
        'title' => 'Send Notification',
        'description' => 'Broadcast a message to all users or a selected group.',
    ])

    @if(session('status'))
    <div class="flex items-start gap-3 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
        <i class="fas fa-check-circle mt-0.5 text-emerald-600"></i>
        <span>{{ session('status') }}</span>
    </div>
    @endif

    <div class="admin-card admin-card-body max-w-2xl">
        <form method="POST" action="{{ route('notifications.send') }}" class="space-y-5">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Title</label>
                <input name="title" required class="admin-input px-4 py-2.5" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Message</label>
                <textarea name="message" rows="4" class="admin-input px-4 py-2.5"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                <div class="flex items-center gap-6 mb-3">
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="scope" value="all" checked class="text-purple-600 focus:ring-purple-500">
                        <span>All users</span>
                    </label>
                    <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                        <input type="radio" name="scope" value="selected" class="text-purple-600 focus:ring-purple-500">
                        <span>Selected users</span>
                    </label>
                </div>
                <div id="selected-users" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Choose users</label>
                    <select name="user_ids[]" multiple size="8" class="admin-select w-full">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Hold Cmd/Ctrl to select multiple users.</p>
                </div>
            </div>
            <div>
                <button type="submit" class="admin-btn-primary">Send</button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const radios = document.querySelectorAll('input[name="scope"]');
    const selectedUsers = document.getElementById('selected-users');
    const toggle = () => {
        const val = document.querySelector('input[name="scope"]:checked').value;
        selectedUsers.classList.toggle('hidden', val !== 'selected');
    };
    radios.forEach(r => r.addEventListener('change', toggle));
    toggle();
});
</script>
@endsection
