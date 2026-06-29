<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\UserExam;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class OrderAdminController extends Controller
{
    public function index(Request $request)
    {
        $status = $request->get('status');
        $ordersQuery = Order::with('user');

        if ($status) {
            $ordersQuery->where('payment_status', $status);
        }

        $orders = $ordersQuery->orderBy('created_at', 'desc')->paginate(10);

        return view('admin.orders.index', compact('orders'));
    }

    public function show(int $id)
    {
        $order = Order::with(['user', 'items.exam'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function refund(int $id)
    {
        $order = Order::findOrFail($id);

        if ($order->payment_status === 'refunded') {
            return back()->with('error', 'Order is already marked as refunded.');
        }

        // Fulfill Stripe/PayPal refunds calls here (simulated or direct service calls)
        
        // Revoke exam access linked to this order
        UserExam::where('order_id', $order->id)->delete();

        // Update database order status
        $order->update([
            'payment_status' => 'refunded',
        ]);

        ActivityLog::log(auth()->id(), 'refund_order', "Refunded order #{$order->order_number} and revoked user exam accesses.");

        return back()->with('success', 'Order status marked as refunded and user access revoked successfully.');
    }
}
