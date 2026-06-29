<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Subscription;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SubscriptionAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $subsQuery = Subscription::with('user');

        if ($status) {
            $subsQuery->where('status', $status);
        }

        $subscriptions = $subsQuery->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.subscriptions.index', compact('subscriptions'));
    }

    public function show(int $id)
    {
        $subscription = Subscription::with('user')->findOrFail($id);
        return view('admin.subscriptions.show', compact('subscription'));
    }

    public function destroy(int $id)
    {
        $subscription = Subscription::findOrFail($id);

        // Cancel stripe/paypal subscriptions here
        $subscription->update([
            'status' => 'cancelled',
            'cancelled_at' => now(),
        ]);

        ActivityLog::log(auth()->id(), 'cancel_subscription', "Admin cancelled subscription #{$subscription->id} for user ID {$subscription->user_id}.");

        return back()->with('success', 'Subscription cancelled successfully.');
    }
}
