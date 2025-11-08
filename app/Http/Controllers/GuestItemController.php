<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class GuestItemController extends Controller
{
    public function showForm(Request $request)
    {
        $itemType = $request->query('type', 'lost');
        return view('guest.post', compact('itemType'));
    }

    public function submit(Request $request)
    {
        $validated = $request->validate([
            'item_type' => 'required|in:lost,found',
            'location' => 'required|string|max:255',
            'description' => 'required|string|max:1000',
            'tags' => 'nullable|string|max:255',
            'images' => 'required|array|min:1|max:5',
            'images.*' => 'required|file|mimes:jpeg,jpg,png,gif,webp|max:10240',
        ]);

        $storedFiles = [];
        foreach ($request->file('images') as $index => $image) {
            $filename = time() . '_' . $index . '_' . $image->getClientOriginalName();
            $path = $image->storeAs('temp-guest', $filename, 'public');
            $storedFiles[] = $path; // relative to public disk
        }

        $pending = [
            'item_type' => $validated['item_type'],
            'location' => $validated['location'],
            'description' => $validated['description'],
            'tags' => $validated['tags'] ?? null,
            'files' => $storedFiles,
        ];

        $request->session()->put('guest_pending_item', $pending);
        return redirect()->route('register')->with('status', 'Create your account to finish posting your item.');
    }
}


