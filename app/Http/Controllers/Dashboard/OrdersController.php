<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ActivityLog;
use Barryvdh\DomPDF\Facade\Pdf;

class OrdersController extends Controller
{
    /**
     * Display a listing of user orders.
     */
    public function index()
    {
        $siteId = (int) (app(\App\Services\SiteContext::class)->id() ?? config('site.id', 1));
        $orders = Order::where('user_id', auth()->id())
            ->where('site_id', $siteId)
            ->with('items.exam')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('dashboard.orders', compact('orders'));
    }

    /**
     * Generate PDF invoice and download.
     */
    public function invoice(int $id)
    {
        $siteId = (int) (app(\App\Services\SiteContext::class)->id() ?? config('site.id', 1));
        $order = Order::where('id', $id)
            ->where('site_id', $siteId)
            ->where('user_id', auth()->id())
            ->with('items.exam')
            ->firstOrFail();

        // Log action
        ActivityLog::log(auth()->id(), 'download_invoice', "Downloaded invoice for order #{$order->order_number}");

        // Build invoice data array
        $data = [
            'order' => $order,
            'user' => auth()->user(),
        ];

        // Generate PDF using barryvdh/laravel-dompdf
        $pdf = Pdf::loadView('emails.invoice', $data);

        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }
}
