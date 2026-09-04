<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Refund;
use App\Models\OrderTimeline;
use App\Models\UserExam;
use App\Models\User;
use App\Services\AuditLogService;
use App\Services\StripeService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderAdminController extends Controller
{
    /**
     * Display order dashboard overview with statistics, revenue chart data, filters, and paginated orders.
     */
    public function index(Request $request)
    {
        // --- 1. Real Database-driven Statistics ---
        $totalOrders = Order::count();
        $todayOrders = Order::whereDate('created_at', today())->count();
        $thisMonthOrders = Order::whereMonth('created_at', now()->month)->whereYear('created_at', now()->year)->count();
        
        $totalGrossRevenue = (float)Order::whereIn('payment_status', ['paid', 'completed', 'partially_refunded', 'refunded'])->sum('total_amount');
        $totalRefundedAmount = (float)Order::sum('refunded_amount') + (float)Refund::where('status', 'completed')->sum('amount');
        // Prevent double counting if refunds stored in both
        $distinctRefunded = (float)Order::where('payment_status', 'refunded')->sum('total_amount') + (float)Order::where('payment_status', 'partially_refunded')->sum('refunded_amount');
        $netRevenue = max(0.0, $totalGrossRevenue - $distinctRefunded);

        $paidOrders = Order::whereIn('payment_status', ['paid', 'completed'])->count();
        $pendingOrders = Order::whereIn('payment_status', ['pending', 'processing'])->count();
        $refundedOrders = Order::whereIn('payment_status', ['refunded', 'partially_refunded'])->count();
        $failedOrders = Order::whereIn('payment_status', ['failed', 'cancelled'])->count();

        // --- 2. Revenue Overview Chart Data ---
        $chartPeriod = $request->get('chart_period', '30days');
        $chartData = $this->calculateChartData($chartPeriod);

        // --- 3. Filterable Orders Query ---
        $query = Order::with(['user', 'items.exam.vendor', 'coupon', 'refunds']);

        // Search (Order Number, Customer Name, Customer Email, Transaction/Stripe/PayPal ID, Product/Exam Name)
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('stripe_payment_intent_id', 'like', "%{$search}%")
                  ->orWhere('paypal_order_id', 'like', "%{$search}%")
                  ->orWhere('billing_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")
                         ->orWhere('email', 'like', "%{$search}%");
                  })
                  ->orWhereHas('items', function ($iq) use ($search) {
                      $iq->where('plan_name', 'like', "%{$search}%")
                         ->orWhereHas('exam', function ($eq) use ($search) {
                             $eq->where('exam_code', 'like', "%{$search}%")
                                ->orWhere('exam_name', 'like', "%{$search}%");
                         });
                  });
            });
        }

        // Filter: Payment / Order Status
        if ($request->filled('status')) {
            $status = strtolower($request->status);
            if ($status === 'paid') {
                $query->whereIn('payment_status', ['paid', 'completed']);
            } elseif ($status === 'pending') {
                $query->whereIn('payment_status', ['pending', 'processing']);
            } elseif ($status === 'refunded') {
                $query->whereIn('payment_status', ['refunded', 'partially_refunded']);
            } else {
                $query->where('payment_status', $status);
            }
        }

        // Filter: Payment Method
        if ($request->filled('payment_method')) {
            $query->where('payment_method', strtolower($request->payment_method));
        }

        // Filter: Date Range
        if ($request->filled('date_range')) {
            switch ($request->date_range) {
                case 'today':
                    $query->whereDate('created_at', today());
                    break;
                case 'yesterday':
                    $query->whereDate('created_at', today()->subDay());
                    break;
                case '7days':
                    $query->where('created_at', '>=', now()->subDays(7));
                    break;
                case '30days':
                    $query->where('created_at', '>=', now()->subDays(30));
                    break;
                case 'this_month':
                    $query->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year);
                    break;
                case 'last_month':
                    $query->whereMonth('created_at', now()->subMonth()->month)->whereYear('created_at', now()->subMonth()->year);
                    break;
                case 'this_year':
                    $query->whereYear('created_at', now()->year);
                    break;
            }
        }

        // Filter: Custom Date Range
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Filter: Amount Range
        if ($request->filled('min_amount')) {
            $query->where('total_amount', '>=', (float)$request->min_amount);
        }
        if ($request->filled('max_amount')) {
            $query->where('total_amount', '<=', (float)$request->max_amount);
        }

        // --- 4. Sorting ---
        $sortField = $request->get('sort', 'created_at');
        $sortDirection = strtolower($request->get('direction', 'desc')) === 'asc' ? 'asc' : 'desc';
        $allowedSorts = ['id', 'order_number', 'total_amount', 'created_at', 'payment_status', 'payment_method'];

        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        // Dynamic per-page
        $perPage = in_array((int)$request->get('per_page'), [10, 25, 50, 100]) ? (int)$request->get('per_page') : 25;
        $orders = $query->paginate($perPage)->withQueryString();

        // Distinct options for filter selects
        $paymentMethods = Order::distinct()->whereNotNull('payment_method')->pluck('payment_method')->filter()->values();

        return view('admin.orders.index', compact(
            'orders',
            'totalOrders',
            'todayOrders',
            'thisMonthOrders',
            'netRevenue',
            'paidOrders',
            'pendingOrders',
            'refundedOrders',
            'failedOrders',
            'chartPeriod',
            'chartData',
            'paymentMethods',
            'sortField',
            'sortDirection',
            'perPage'
        ));
    }

    /**
     * Calculate timeseries chart data for revenue and order volume.
     */
    protected function calculateChartData(string $period): array
    {
        $days = match ($period) {
            'today' => 1,
            '7days' => 7,
            '30days', 'this_month' => 30,
            'last_month' => 60,
            'this_year' => 365,
            default => 30,
        };

        $startDate = match ($period) {
            'today' => today()->startOfDay(),
            '7days' => now()->subDays(6)->startOfDay(),
            '30days' => now()->subDays(29)->startOfDay(),
            'this_month' => now()->startOfMonth(),
            'last_month' => now()->subMonth()->startOfMonth(),
            'this_year' => now()->startOfYear(),
            default => now()->subDays(29)->startOfDay(),
        };

        $paidOrders = Order::where('created_at', '>=', $startDate)
            ->whereIn('payment_status', ['paid', 'completed', 'partially_refunded'])
            ->get();

        $points = [];
        $current = clone $startDate;
        $now = now();

        if ($period === 'today') {
            // Group by 4-hour intervals
            for ($h = 0; $h < 24; $h += 4) {
                $label = sprintf('%02d:00', $h);
                $endH = $h + 4;
                $matching = $paidOrders->filter(fn($o) => $o->created_at->hour >= $h && $o->created_at->hour < $endH);
                $points[] = [
                    'label' => $label,
                    'revenue' => round($matching->sum('total_amount'), 2),
                    'orders' => $matching->count(),
                ];
            }
        } elseif ($period === 'this_year') {
            // Group by months
            for ($m = 1; $m <= 12; $m++) {
                $monthDate = Carbon::create(now()->year, $m, 1);
                if ($monthDate->isFuture()) break;
                $label = $monthDate->format('M');
                $matching = $paidOrders->filter(fn($o) => $o->created_at->month === $m);
                $points[] = [
                    'label' => $label,
                    'revenue' => round($matching->sum('total_amount'), 2),
                    'orders' => $matching->count(),
                ];
            }
        } else {
            // Group by day
            while ($current <= $now) {
                $dateKey = $current->format('Y-m-d');
                $label = $current->format('M d');
                $matching = $paidOrders->filter(fn($o) => $o->created_at->format('Y-m-d') === $dateKey);
                $points[] = [
                    'label' => $label,
                    'revenue' => round($matching->sum('total_amount'), 2),
                    'orders' => $matching->count(),
                ];
                $current->addDay();
            }
        }

        $maxRevenue = !empty($points) ? max(array_column($points, 'revenue')) : 100;
        $maxOrders = !empty($points) ? max(array_column($points, 'orders')) : 5;

        return [
            'points' => $points,
            'maxRevenue' => max(10, $maxRevenue),
            'maxOrders' => max(1, $maxOrders),
            'totalRevenue' => round($paidOrders->sum('total_amount'), 2),
            'totalCount' => $paidOrders->count(),
        ];
    }

    /**
     * Show comprehensive order details page.
     */
    public function show(int $id)
    {
        $order = Order::with([
            'user',
            'items.exam.vendor',
            'coupon',
            'refunds.admin',
            'timelines.performer',
            'userExams.exam.vendor',
        ])->findOrFail($id);

        // Calculate Customer Metrics
        $customerOrdersCount = Order::where('user_id', $order->user_id)->count();
        $customerTotalSpent = (float)Order::where('user_id', $order->user_id)
            ->whereIn('payment_status', ['paid', 'completed'])
            ->sum('total_amount');

        return view('admin.orders.show', compact('order', 'customerOrdersCount', 'customerTotalSpent'));
    }

    /**
     * Process a Full or Partial refund.
     */
    public function refund(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'refund_type' => 'required|in:full,partial',
            'amount' => 'nullable|numeric|min:0.01',
            'reason' => 'nullable|string|max:255',
            'revoke_access' => 'nullable|boolean',
        ]);

        $remaining = $order->remainingRefundableAmount();

        if ($remaining <= 0) {
            return back()->with('error', 'This order has already been fully refunded.');
        }

        $refundAmount = $request->refund_type === 'full' ? $remaining : (float)$request->amount;

        if ($refundAmount > $remaining) {
            return back()->with('error', "Refund amount (\${$refundAmount}) exceeds the remaining refundable amount (\${$remaining}).");
        }

        // Process refund via StripeService if stripe
        $gatewayRefundId = null;
        if ($order->payment_method === 'stripe' && $order->stripe_payment_intent_id) {
            $stripeService = new StripeService();
            $stripeResult = $stripeService->refundPayment($order->stripe_payment_intent_id, $refundAmount, $request->reason);

            if (empty($stripeResult['success'])) {
                return back()->with('error', 'Payment gateway error: ' . ($stripeResult['error'] ?? 'Refund could not be processed.'));
            }
            $gatewayRefundId = $stripeResult['refund_id'] ?? null;
        }

        // Record Refund in Database
        $refund = Refund::create([
            'order_id' => $order->id,
            'admin_id' => auth()->id(),
            'amount' => $refundAmount,
            'currency' => 'USD',
            'reason' => $request->reason ?: 'Admin initiated refund',
            'status' => 'completed',
            'gateway_refund_id' => $gatewayRefundId,
        ]);

        // Update Order's Refunded Amount & Payment Status
        $newRefundedTotal = (float)$order->refunded_amount + $refundAmount;
        $isFull = $newRefundedTotal >= (float)$order->total_amount;

        $order->update([
            'refunded_amount' => $newRefundedTotal,
            'payment_status' => $isFull ? 'refunded' : 'partially_refunded',
        ]);

        // Revoke certification access if requested or if full refund
        if ($request->boolean('revoke_access') || $isFull) {
            UserExam::where('order_id', $order->id)->delete();
            OrderTimeline::record($order->id, 'access_revoked', 'Certification access privileges revoked due to refund.');
        }

        // Log Order Timeline
        OrderTimeline::record($order->id, 'refund_processed', "Issued " . ($isFull ? "Full" : "Partial") . " Refund of \${$refundAmount}. Reason: " . ($request->reason ?: 'None specified'), auth()->id(), [
            'refund_id' => $refund->id,
            'amount' => $refundAmount,
            'gateway_refund_id' => $gatewayRefundId,
        ]);

        // Centralized Audit Log
        AuditLogService::log('order_refunded', "Processed \${$refundAmount} refund for order #{$order->order_number}", $order->user_id, [
            'order_number' => $order->order_number,
            'amount' => $refundAmount,
            'type' => $request->refund_type,
            'gateway_id' => $gatewayRefundId,
        ]);

        return back()->with('success', "Successfully refunded \${$refundAmount} for order #{$order->order_number}.");
    }

    /**
     * Update order payment or fulfillment status.
     */
    public function updateStatus(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'payment_status' => 'required|in:paid,pending,completed,failed,cancelled,refunded,partially_refunded',
        ]);

        $oldStatus = $order->payment_status;
        $order->payment_status = $request->payment_status;
        $order->save();

        OrderTimeline::record($order->id, 'status_updated', "Status updated from '{$oldStatus}' to '{$request->payment_status}'.", auth()->id());
        AuditLogService::log('order_status_updated', "Updated order #{$order->order_number} status to '{$request->payment_status}'", $order->user_id);

        return back()->with('success', "Order #{$order->order_number} status updated to '{$request->payment_status}'.");
    }

    /**
     * Update private admin notes for this order.
     */
    public function updateNotes(Request $request, int $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'admin_notes' => 'nullable|string|max:2000',
        ]);

        $order->admin_notes = $request->admin_notes;
        $order->save();

        return back()->with('success', 'Internal administrator notes saved.');
    }

    /**
     * Download PDF invoice using barryvdh/laravel-dompdf.
     */
    public function downloadInvoice(int $id)
    {
        $order = Order::with(['user', 'items.exam'])->findOrFail($id);

        OrderTimeline::record($order->id, 'invoice_downloaded', 'Administrator generated and downloaded PDF invoice.', auth()->id());

        $pdf = Pdf::loadView('emails.invoice', [
            'order' => $order,
            'user' => $order->user,
        ]);

        return $pdf->download('invoice-' . $order->order_number . '.pdf');
    }

    /**
     * Print-friendly HTML view of order invoice.
     */
    public function printInvoice(int $id)
    {
        $order = Order::with(['user', 'items.exam.vendor', 'coupon', 'refunds'])->findOrFail($id);

        return view('admin.orders.print', compact('order'));
    }

    /**
     * Resend order confirmation email.
     */
    public function resendConfirmation(int $id)
    {
        $order = Order::with(['user', 'items.exam'])->findOrFail($id);

        try {
            if (class_exists(\App\Mail\OrderConfirmationMail::class) && $order->billing_email) {
                Mail::to($order->billing_email)->send(new \App\Mail\OrderConfirmationMail($order));
            }
            OrderTimeline::record($order->id, 'confirmation_resent', "Order confirmation email resent to {$order->billing_email}.", auth()->id());
            return back()->with('success', "Confirmation email dispatched to {$order->billing_email}.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to dispatch email: ' . $e->getMessage());
        }
    }

    /**
     * Bulk Action handler for orders.
     */
    public function bulkAction(Request $request)
    {
        $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'exists:orders,id',
            'action' => 'required|in:mark_completed,mark_cancelled,export',
        ]);

        $orders = Order::whereIn('id', $request->order_ids)->get();

        if ($request->action === 'mark_completed') {
            Order::whereIn('id', $request->order_ids)->update(['payment_status' => 'completed']);
            foreach ($orders as $o) {
                OrderTimeline::record($o->id, 'status_updated', "Bulk marked as Completed.", auth()->id());
            }
            return back()->with('success', count($request->order_ids) . ' orders marked as completed.');
        }

        if ($request->action === 'mark_cancelled') {
            Order::whereIn('id', $request->order_ids)->update(['payment_status' => 'cancelled']);
            foreach ($orders as $o) {
                OrderTimeline::record($o->id, 'status_updated', "Bulk marked as Cancelled.", auth()->id());
            }
            return back()->with('success', count($request->order_ids) . ' orders marked as cancelled.');
        }

        if ($request->action === 'export') {
            return $this->streamExportCsv($orders);
        }

        return back();
    }

    /**
     * Filter-aware CSV export stream.
     */
    public function export(Request $request)
    {
        $query = Order::with(['user', 'items.exam', 'coupon']);

        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('billing_name', 'like', "%{$search}%")
                  ->orWhere('billing_email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('payment_status', strtolower($request->status));
        }

        if ($request->filled('payment_method')) {
            $query->where('payment_method', strtolower($request->payment_method));
        }

        $orders = $query->orderBy('id', 'desc')->get();

        return $this->streamExportCsv($orders);
    }

    /**
     * Stream CSV download response.
     */
    protected function streamExportCsv($orders): StreamedResponse
    {
        $fileName = 'orders-export-' . date('Y-m-d-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$fileName\"",
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0',
        ];

        return response()->stream(function () use ($orders) {
            $file = fopen('php://output', 'w');

            // Header row
            fputcsv($file, [
                'Order ID',
                'Order Number',
                'Customer Name',
                'Customer Email',
                'Items Count',
                'Items Summary',
                'Subtotal (USD)',
                'Discount (USD)',
                'Total Amount (USD)',
                'Refunded Amount (USD)',
                'Payment Method',
                'Payment Status',
                'Stripe/PayPal Transaction ID',
                'Created Date',
            ]);

            foreach ($orders as $order) {
                $itemsDesc = $order->items->map(function ($it) {
                    return ($it->exam ? $it->exam->exam_code : $it->plan_name) . " (\${$it->price})";
                })->implode('; ');

                fputcsv($file, [
                    $order->id,
                    $order->order_number,
                    $order->user ? $order->user->name : $order->billing_name,
                    $order->user ? $order->user->email : $order->billing_email,
                    $order->items->count(),
                    $itemsDesc,
                    number_format($order->subtotal, 2, '.', ''),
                    number_format($order->discount_amount, 2, '.', ''),
                    number_format($order->total_amount, 2, '.', ''),
                    number_format($order->refunded_amount ?? 0, 2, '.', ''),
                    strtoupper($order->payment_method ?? 'N/A'),
                    ucfirst($order->payment_status ?? 'Pending'),
                    $order->stripe_payment_intent_id ?: ($order->paypal_order_id ?: 'N/A'),
                    $order->created_at ? $order->created_at->format('Y-m-d H:i:s') : '',
                ]);
            }

            fclose($file);
        }, 200, $headers);
    }
}
