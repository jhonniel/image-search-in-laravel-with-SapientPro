<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ContactHelpSection;
use App\Models\ContactRequest;
use Illuminate\Http\Request;

class ContactRequestController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');

        $query = ContactRequest::query()->orderBy('created_at', 'desc');

        if ($status !== 'all') {
            $query->where('status', $status);
        }

        $contactRequests = $query->paginate(15)->withQueryString();
        $helpSections = ContactHelpSection::orderBy('display_order')->get();

        $stats = [
            'total' => ContactRequest::count(),
            'pending' => ContactRequest::where('status', 'pending')->count(),
            'in_progress' => ContactRequest::where('status', 'in_progress')->count(),
            'resolved' => ContactRequest::where('status', 'resolved')->count(),
        ];

        return view('admin.contact-requests.index', compact('contactRequests', 'status', 'helpSections', 'stats'));
    }

    public function update(Request $request, ContactRequest $contactRequest)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,in_progress,resolved',
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $contactRequest->status = $validated['status'];
        $contactRequest->admin_notes = $validated['admin_notes'] ?? null;
        $contactRequest->resolved_at = $validated['status'] === 'resolved' ? now() : null;
        $contactRequest->save();

        return redirect()->route('contact-requests.index')
            ->with('success', 'Contact request updated successfully.');
    }

    public function upsertHelpSection(Request $request)
    {
        $validated = $request->validate([
            'id' => 'nullable|exists:contact_help_sections,id',
            'heading' => 'required|string|max:255',
            'body' => 'required|string',
            'cta_label' => 'nullable|string|max:255',
            'cta_url' => 'nullable|url|max:255',
            'is_active' => 'sometimes|boolean',
            'display_order' => 'nullable|integer|min:0',
        ]);

        ContactHelpSection::updateOrCreate(
            ['id' => $validated['id'] ?? null],
            [
                'heading' => $validated['heading'],
                'body' => $validated['body'],
                'cta_label' => $validated['cta_label'] ?? null,
                'cta_url' => $validated['cta_url'] ?? null,
                'is_active' => $request->has('is_active'),
                'display_order' => $validated['display_order'] ?? 0,
            ]
        );

        return redirect()->route('contact-requests.index')
            ->with('success', 'Contact help section saved.');
    }

    public function deleteHelpSection(ContactHelpSection $section)
    {
        $section->delete();

        return redirect()->route('contact-requests.index')
            ->with('success', 'Contact help section removed.');
    }
}

