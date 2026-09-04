<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Media;
use Illuminate\Support\Facades\Storage;

class MediaController extends Controller
{
    public function index(Request $request)
    {
        $media = Media::latest()->paginate(24);
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'data' => $media->items(),
                'next_page_url' => $media->nextPageUrl(),
            ]);
        }
        return view('admin.media.index', compact('media'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:10240', // max 10MB
        ]);

        $file = $request->file('file');
        
        $path = $file->store('media', 'public');
        
        $media = Media::create([
            'user_id' => auth()->id(),
            'name' => $file->getClientOriginalName(),
            'file_name' => $file->getClientOriginalName(),
            'mime_type' => $file->getMimeType(),
            'path' => $path,
            'size' => $file->getSize(),
        ]);

        return response()->json([
            'success' => true,
            'media' => $media,
            'url' => $media->url
        ]);
    }

    public function destroy(Media $medium)
    {
        if (Storage::disk('public')->exists($medium->path)) {
            Storage::disk('public')->delete($medium->path);
        }
        
        $medium->delete();
        
        return back()->with('success', 'File deleted successfully.');
    }
}
