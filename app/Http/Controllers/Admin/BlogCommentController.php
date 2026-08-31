<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogComment;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $query = BlogComment::with('post')->latest();

        if ($request->has('status') && $request->status !== 'all') {
            $query->where('status', $request->status);
        }

        $comments = $query->paginate(20);
        return view('admin.blog.comments.index', compact('comments'));
    }

    public function approve(BlogComment $comment)
    {
        $comment->update(['status' => 'approved']);
        return back()->with('success', 'Comment approved.');
    }

    public function spam(BlogComment $comment)
    {
        $comment->update(['status' => 'spam']);
        return back()->with('success', 'Comment marked as spam.');
    }

    public function destroy(BlogComment $comment)
    {
        $comment->delete();
        return back()->with('success', 'Comment deleted.');
    }
}
