<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\BlogSubscriber;

class BlogSubscriberController extends Controller
{
    public function index()
    {
        $subscribers = BlogSubscriber::latest('subscribed_at')->paginate(20);
        return view('admin.blog.subscribers.index', compact('subscribers'));
    }
}
