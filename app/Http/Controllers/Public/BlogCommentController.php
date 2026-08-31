<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogComment;

class BlogCommentController extends Controller
{
    public function store(Request $request)
    {
        // Simple honeypot
        if ($request->filled('website_url_honeypot')) {
            return back()->with('error', 'Spam detected.');
        }

        $validated = $request->validate([
            'blog_post_id' => 'required|exists:blog_posts,id',
            'author_name' => 'required|string|max:255',
            'author_email' => 'required|email|max:255',
            'comment_text' => 'required|string',
            'parent_id' => 'nullable|exists:blog_comments,id',
        ]);

        $validated['status'] = 'pending';

        BlogComment::create($validated);

        return back()->with('success', 'Your comment has been submitted and is awaiting moderation. Thank you!');
    }
}
