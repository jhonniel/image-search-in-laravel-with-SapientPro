@extends('layouts.admin')

@section('title', 'Send Notifications - Admin')

@section('content')
<div class="space-y-6">
    <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-6">
        <h2 class="text-xl font-semibold text-gray-900 mb-4">Send Notification</h2>
        @if(session('status'))
            <div class="mb-4 px-4 py-2 bg-green-50 text-green-700 rounded">{{ session('status') }}</div>
        @endif
        <form method="POST" action="{{ route('notifications.send') }}" class="space-y-4">
            @csrf
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Title</label>
                <input name="title" required class="w-full border-gray-300 rounded-lg" />
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Message</label>
                <textarea name="message" rows="4" class="w-full border-gray-300 rounded-lg"></textarea>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Recipients</label>
                <div class="flex items-center gap-4 mb-3">
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="scope" value="all" checked>
                        <span>All users</span>
                    </label>
                    <label class="inline-flex items-center gap-2">
                        <input type="radio" name="scope" value="selected">
                        <span>Selected users</span>
                    </label>
                </div>
                <div id="selected-users" class="hidden">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Choose users</label>
                    <select name="user_ids[]" multiple size="8" class="w-full border-gray-300 rounded-lg">
                        @foreach($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-500 mt-1">Hold Cmd/Ctrl to select multiple users.</p>
                </div>
            </div>
            <div>
                <button class="px-4 py-2 bg-purple-primary text-white rounded-lg">Send</button>
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
