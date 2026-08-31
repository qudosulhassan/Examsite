<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogSubscriber;

class BlogSubscriberController extends Controller
{
    public function subscribe(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'source' => 'nullable|string'
        ]);

        $existing = BlogSubscriber::where('email', $validated['email'])->first();

        if ($existing) {
            if ($existing->status === 'unsubscribed') {
                $existing->update(['status' => 'active', 'subscribed_at' => now(), 'unsubscribed_at' => null]);
                return back()->with('success', 'Welcome back! You have been re-subscribed.');
            }
            return back()->with('info', 'You are already subscribed!');
        }

        BlogSubscriber::create([
            'first_name' => $validated['first_name'],
            'email' => $validated['email'],
            'source' => $validated['source'] ?? 'unknown',
            'ip_address' => $request->ip(),
        ]);

        return back()->with('success', 'Thank you for subscribing! We will send you updates soon.');
    }

    public function unsubscribe(Request $request)
    {
        $email = $request->query('email');
        if ($email) {
            BlogSubscriber::where('email', $email)->update([
                'status' => 'unsubscribed',
                'unsubscribed_at' => now(),
            ]);
            return redirect('/blog')->with('success', 'You have been unsubscribed successfully.');
        }
        return redirect('/blog');
    }
}
