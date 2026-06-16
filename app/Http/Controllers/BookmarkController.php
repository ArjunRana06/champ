<?php

namespace App\Http\Controllers;

use App\Models\Bookmark;
use Illuminate\Http\Request;

class BookmarkController extends Controller
{
    public function index()
    {
        $bookmarks = Bookmark::forUser(auth()->id())
            ->latest()
            ->get()
            ->groupBy('bookmarkable_type');

        return view('Backend.bookmarks.index', compact('bookmarks'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'bookmarkable_type' => 'required|string',
            'bookmarkable_id' => 'required|integer',
            'label' => 'nullable|string|max:100',
        ]);

        $userId = auth()->id();
        $existing = Bookmark::forUser($userId)
            ->where('bookmarkable_type', $request->bookmarkable_type)
            ->where('bookmarkable_id', $request->bookmarkable_id)
            ->first();

        if ($existing) {
            $existing->delete();
            return response()->json(['bookmarked' => false]);
        }

        Bookmark::create([
            'user_id' => $userId,
            'bookmarkable_type' => $request->bookmarkable_type,
            'bookmarkable_id' => $request->bookmarkable_id,
            'label' => $request->label,
        ]);

        return response()->json(['bookmarked' => true]);
    }

    public function destroy($id)
    {
        $bookmark = Bookmark::forUser(auth()->id())->findOrFail($id);
        $bookmark->delete();
        return back()->with('success', 'Bookmark removed.');
    }
}
