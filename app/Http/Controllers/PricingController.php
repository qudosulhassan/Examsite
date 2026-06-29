<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Package;

class PricingController extends Controller
{
    public function index()
    {
        $subscriptions = Package::whereNull('vendor_id')
                                ->where('type', 'subscription')
                                ->where('is_active', true)
                                ->orderBy('sort_order', 'asc')
                                ->get();
                                
        $bundles = Package::whereNull('vendor_id')
                          ->where('type', 'bundle')
                          ->where('is_active', true)
                          ->orderBy('sort_order', 'asc')
                          ->get();

        return view('pages.pricing', compact('subscriptions', 'bundles'));
    }
}
