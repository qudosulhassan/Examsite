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
        $orders = Order::where('user_id', auth()->id())
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
        $order = Order::where('id', $id)
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
