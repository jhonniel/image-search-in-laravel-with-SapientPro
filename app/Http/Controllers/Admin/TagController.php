<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tag;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TagController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $tags = Tag::orderBy('usage_count', 'desc')
            ->orderBy('name', 'asc')
            ->paginate(50);
        
        return view('admin.tags.index', compact('tags'));
    }

    /**
     * Get all tags as JSON (for API)
     */
    public function getAllTags()
    {
        $tags = Tag::orderBy('usage_count', 'desc')
            ->orderBy('name', 'asc')
            ->get(['id', 'name', 'usage_count']);
        
        return response()->json($tags);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $tag = Tag::create([
            'name' => trim($request->name),
            'usage_count' => 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag created successfully',
            'tag' => $tag
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tag $tag)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255|unique:tags,name,' . $tag->id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first()
            ], 422);
        }

        $tag->update([
            'name' => trim($request->name),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Tag updated successfully',
            'tag' => $tag
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tag $tag)
    {
        $tag->delete();

        return response()->json([
            'success' => true,
            'message' => 'Tag deleted successfully'
        ]);
    }
}
