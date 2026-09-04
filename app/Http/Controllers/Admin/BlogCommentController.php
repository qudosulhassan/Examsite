<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogComment;

class BlogCommentController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $counts = [
            'all' => BlogComment::count(),
            'pending' => BlogComment::where('status', 'pending')->count(),
            'approved' => BlogComment::where('status', 'approved')->count(),
            'spam' => BlogComment::where('status', 'spam')->count(),
            'trash' => BlogComment::onlyTrashed()->count(),
        ];

        if ($status === 'trash') {
            $query = BlogComment::onlyTrashed()->with(['post', 'parent']);
        } else {
            $query = BlogComment::with(['post', 'parent']);
            if ($status !== 'all' && in_array($status, ['pending', 'approved', 'spam'])) {
                $query->where('status', $status);
            }
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('author_name', 'like', "%{$search}%")
                  ->orWhere('author_email', 'like', "%{$search}%")
                  ->orWhere('comment_text', 'like', "%{$search}%")
                  ->orWhereHas('post', function ($postQ) use ($search) {
                      $postQ->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $comments = $query->latest('id')->paginate(20)->withQueryString();
        return view('admin.blog.comments.index', compact('comments', 'counts', 'status', 'search'));
    }

    public function approve($id)
    {
        $comment = BlogComment::withTrashed()->findOrFail($id);
        if ($comment->trashed()) {
            $comment->restore();
        }
        $comment->update(['status' => 'approved']);
        return back()->with('success', 'Comment approved successfully.');
    }

    public function spam($id)
    {
        $comment = BlogComment::withTrashed()->findOrFail($id);
        if ($comment->trashed()) {
            $comment->restore();
        }
        $comment->update(['status' => 'spam']);
        return back()->with('success', 'Comment marked as spam.');
    }

    public function pending($id)
    {
        $comment = BlogComment::withTrashed()->findOrFail($id);
        if ($comment->trashed()) {
            $comment->restore();
        }
        $comment->update(['status' => 'pending']);
        return back()->with('success', 'Comment marked as pending moderation.');
    }

    public function reply(Request $request, $id)
    {
        $request->validate([
            'reply_text' => 'required|string|max:3000',
        ]);

        $parentComment = BlogComment::findOrFail($id);

        BlogComment::create([
            'blog_post_id' => $parentComment->blog_post_id,
            'author_name' => auth()->user()->name ?? 'Editorial Staff',
            'author_email' => auth()->user()->email ?? 'admin@examtopicsbase.com',
            'comment_text' => $request->reply_text,
            'parent_id' => $parentComment->id,
            'status' => 'approved',
        ]);

        return back()->with('success', 'Admin reply posted successfully.');
    }

    public function destroy($id)
    {
        $comment = BlogComment::findOrFail($id);
        $comment->delete(); // Soft delete to Trash
        return back()->with('success', 'Comment moved to trash.');
    }

    public function restore($id)
    {
        $comment = BlogComment::onlyTrashed()->findOrFail($id);
        $comment->restore();
        return back()->with('success', 'Comment restored from trash.');
    }

    public function forceDelete($id)
    {
        $comment = BlogComment::onlyTrashed()->findOrFail($id);
        $comment->forceDelete();
        return back()->with('success', 'Comment permanently deleted.');
    }

    public function bulkAction(Request $request)
    {
        $request->validate([
            'comment_ids' => 'required|array',
            'comment_ids.*' => 'integer',
            'action' => 'required|in:approve,spam,pending,trash,restore,force_delete',
        ]);

        $ids = $request->comment_ids;
        $action = $request->action;

        if ($action === 'approve') {
            BlogComment::withTrashed()->whereIn('id', $ids)->restore();
            BlogComment::whereIn('id', $ids)->update(['status' => 'approved']);
            return back()->with('success', count($ids) . ' comment(s) approved.');
        } elseif ($action === 'spam') {
            BlogComment::withTrashed()->whereIn('id', $ids)->restore();
            BlogComment::whereIn('id', $ids)->update(['status' => 'spam']);
            return back()->with('success', count($ids) . ' comment(s) marked as spam.');
        } elseif ($action === 'pending') {
            BlogComment::withTrashed()->whereIn('id', $ids)->restore();
            BlogComment::whereIn('id', $ids)->update(['status' => 'pending']);
            return back()->with('success', count($ids) . ' comment(s) set to pending.');
        } elseif ($action === 'trash') {
            BlogComment::whereIn('id', $ids)->delete();
            return back()->with('success', count($ids) . ' comment(s) moved to trash.');
        } elseif ($action === 'restore') {
            BlogComment::onlyTrashed()->whereIn('id', $ids)->restore();
            return back()->with('success', count($ids) . ' comment(s) restored.');
        } elseif ($action === 'force_delete') {
            BlogComment::onlyTrashed()->whereIn('id', $ids)->forceDelete();
            return back()->with('success', count($ids) . ' comment(s) permanently deleted.');
        }

        return back();
    }
}