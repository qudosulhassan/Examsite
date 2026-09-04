<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogSubscriber;

class BlogSubscriberController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status', 'all');
        $search = $request->get('search');

        $counts = [
            'all' => BlogSubscriber::count(),
            'active' => BlogSubscriber::where('status', 'active')->count(),
            'unsubscribed' => BlogSubscriber::where('status', 'unsubscribed')->count(),
        ];

        $query = BlogSubscriber::latest('subscribed_at');

        if ($status !== 'all' && in_array($status, ['active', 'unsubscribed'])) {
            $query->where('status', $status);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('first_name', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $subscribers = $query->paginate(25)->withQueryString();
        return view('admin.blog.subscribers.index', compact('subscribers', 'counts', 'status', 'search'));
    }

    public function toggleStatus($id)
    {
        $subscriber = BlogSubscriber::findOrFail($id);
        if ($subscriber->status === 'active') {
            $subscriber->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
            return back()->with('success', "Subscriber {$subscriber->email} marked as unsubscribed.");
        } else {
            $subscriber->update([
                'status' => 'active',
                'unsubscribed_at' => null,
            ]);
            return back()->with('success', "Subscriber {$subscriber->email} reactivated.");
        }
    }

    public function destroy($id)
    {
        $subscriber = BlogSubscriber::findOrFail($id);
        $subscriber->delete();
        return back()->with('success', 'Subscriber removed successfully.');
    }

    public function exportCsv(Request $request)
    {
        $status = $request->get('status', 'all');
        $query = BlogSubscriber::latest('subscribed_at');

        if ($status !== 'all' && in_array($status, ['active', 'unsubscribed'])) {
            $query->where('status', $status);
        }

        $filename = 'blog_subscribers_' . date('Y-m-d_His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        $callback = function () use ($query) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'First Name', 'Email', 'Status', 'Subscribed At', 'Unsubscribed At', 'Source', 'IP Address']);

            $query->chunk(200, function ($subscribers) use ($file) {
                foreach ($subscribers as $sub) {
                    fputcsv($file, [
                        $sub->id,
                        $sub->first_name ?? '',
                        $sub->email,
                        $sub->status,
                        $sub->subscribed_at ? $sub->subscribed_at->format('Y-m-d H:i:s') : '',
                        $sub->unsubscribed_at ? $sub->unsubscribed_at->format('Y-m-d H:i:s') : '',
                        $sub->source ?? '',
                        $sub->ip_address ?? '',
                    ]);
                }
            });

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}