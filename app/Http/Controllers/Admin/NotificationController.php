<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    public function create()
    {
        $users = User::orderBy('name')->get(['id','name','email']);
        return view('admin.notifications.create', compact('users'));
    }

    public function send(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:255',
            'message' => 'nullable|string',
            'scope' => 'required|in:selected,all',
            'user_ids' => 'array',
            'user_ids.*' => 'integer|exists:users,id',
        ]);

        $query = $data['scope'] === 'all' ? User::query() : User::whereIn('id', $data['user_ids'] ?? []);
        $users = $query->get(['id']);

        foreach ($users as $user) {
            Notification::create([
                'user_id' => $user->id,
                'type' => 'admin_broadcast',
                'title' => $data['title'],
                'message' => $data['message'] ?? null,
                'data' => null,
            ]);
        }

        return redirect()->back()->with('status', 'Notification(s) sent successfully');
    }
}
