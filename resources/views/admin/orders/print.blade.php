<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Print Invoice - #{{ $order->order_number }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: #F8FAFC;
            color: #0A1628;
            font-size: 13px;
            line-height: 1.5;
            padding: 40px 20px;
        }
        .invoice-card {
            max-width: 800px;
            margin: 0 auto;
            background: #FFFFFF;
            border-radius: 16px;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 48px;
        }
        .header-row {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid #F1F5F9;
            padding-bottom: 32px;
            margin-bottom: 32px;
        }
        .brand-logo {
            font-size: 24px;
            font-weight: 900;
            color: #0A1628;
            letter-spacing: -0.5px;
        }
        .brand-logo span { color: #00D4AA; }
        .badge-status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 9999px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-top: 8px;
        }
        .badge-completed { background: #ECFDF5; color: #047857; border: 1px solid #A7F3D0; }
        .badge-refunded { background: #FAF5FF; color: #7E22CE; border: 1px solid #E9D5FF; }
        .badge-pending { background: #FFFBEB; color: #B45309; border: 1px solid #FDE68A; }
        .meta-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 32px;
            margin-bottom: 40px;
        }
        .meta-block h4 {
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #94A3B8;
            margin-bottom: 8px;
        }
        .meta-block p {
            font-size: 13px;
            color: #334155;
            line-height: 1.6;
        }
        table.items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 32px;
        }
        table.items-table th {
            text-align: left;
            padding: 12px 16px;
            background: #F8FAFC;
            border-bottom: 2px solid #E2E8F0;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: #64748B;
        }
        table.items-table td {
            padding: 16px;
            border-bottom: 1px solid #F1F5F9;
            font-size: 13px;
        }
        .totals-block {
            display: flex;
            justify-content: flex-end;
            margin-bottom: 40px;
        }
        .totals-table {
            width: 320px;
        }
        .totals-table tr td {
            padding: 6px 0;
            font-size: 13px;
        }
        .totals-table tr td:last-child {
            text-align: right;
            font-family: 'JetBrains Mono', monospace;
        }
        .totals-table tr.total-row td {
            border-top: 2px solid #0A1628;
            padding-top: 12px;
            font-size: 16px;
            font-weight: 900;
            color: #0A1628;
        }
        .footer-note {
            text-align: center;
            padding-top: 32px;
            border-top: 1px solid #F1F5F9;
            color: #94A3B8;
            font-size: 11px;
        }
        .print-toolbar {
            max-width: 800px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
        }
        .btn-primary { background: #0A1628; color: #FFFFFF; }
        .btn-primary:hover { background: #1E293B; }
        .btn-secondary { background: #FFFFFF; color: #475569; border-color: #E2E8F0; }
        .btn-secondary:hover { background: #F8FAFC; }

        @media print {
            body { background: #FFFFFF; padding: 0; }
            .invoice-card { border: none; box-shadow: none; padding: 0; max-width: 100%; }
            .print-toolbar { display: none !important; }
        }
    </style>
</head>
<body>

    <div class="print-toolbar">
        <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-secondary">
            ← Back to Order
        </a>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.orders.invoice', $order->id) }}" class="btn btn-secondary">
                Download PDF
            </a>
            <button onclick="window.print()" class="btn btn-primary">
                🖨 Print Invoice
            </button>
        </div>
    </div>

    <div class="invoice-card">
        <!-- Header -->
        <div class="header-row">
            <div>
                <div class="brand-logo">ExamTopics<span>Base</span></div>
                <div style="color: #64748B; font-size: 12px; margin-top: 4px;">Premium IT Certification Preparation & Mock Testing</div>
                <div style="color: #94A3B8; font-size: 11px; margin-top: 2px;">support@examtopicsbase.com &bull; 30-Day Guarantee</div>
            </div>
            <div style="text-align: right;">
                <h1 style="font-size: 20px; font-weight: 900; color: #0A1628; font-family: 'JetBrains Mono', monospace;">INVOICE</h1>
                <div style="font-family: 'JetBrains Mono', monospace; font-size: 13px; font-weight: 700; color: #475569; margin-top: 2px;">
                    #{{ $order->order_number }}
                </div>
                <div style="color: #64748B; font-size: 12px; margin-top: 4px;">
                    Date: {{ $order->created_at->format('F d, Y') }}
                </div>
                <div>
                    @if(in_array($order->payment_status, ['paid', 'completed']))
                        <span class="badge-status badge-completed">Paid In Full</span>
                    @elseif(in_array($order->payment_status, ['refunded', 'partially_refunded']))
                        <span class="badge-status badge-refunded">{{ strtoupper(str_replace('_', ' ', $order->payment_status)) }}</span>
                    @else
                        <span class="badge-status badge-pending">{{ strtoupper($order->payment_status) }}</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- Meta Grid -->
        <div class="meta-grid">
            <div class="meta-block">
                <h4>Billed To</h4>
                <p>
                    <strong>{{ $order->user ? $order->user->name : ($order->billing_name ?: 'Customer') }}</strong><br>
                    {{ $order->user ? $order->user->email : $order->billing_email }}<br>
                    @if($order->user && $order->user->phone)
                        Tel: {{ $order->user->phone }}<br>
                    @endif
                    Customer ID: #{{ $order->user_id ?? 'N/A' }}
                </p>
            </div>
            <div class="meta-block" style="text-align: right;">
                <h4>Payment Details</h4>
                <p>
                    Method: <strong>{{ strtoupper($order->payment_method) }}</strong><br>
                    @if($order->stripe_payment_intent_id)
                        <span style="font-family: 'JetBrains Mono', monospace; font-size: 11px;">Stripe: {{ $order->stripe_payment_intent_id }}</span><br>
                    @endif
                    @if($order->paypal_order_id)
                        <span style="font-family: 'JetBrains Mono', monospace; font-size: 11px;">PayPal: {{ $order->paypal_order_id }}</span><br>
                    @endif
                    Currency: USD ($)<br>
                    Processed: {{ $order->created_at->format('M d, Y h:i A') }}
                </p>
            </div>
        </div>

        <!-- Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th>Item Description</th>
                    <th style="width: 100px;">Type</th>
                    <th style="width: 80px; text-align: center;">Qty</th>
                    <th style="width: 120px; text-align: right;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->items as $item)
                    <tr>
                        <td>
                            @if($item->exam)
                                <strong>{{ $item->exam->vendor ? $item->exam->vendor->name : 'IT' }} {{ $item->exam->exam_code }}</strong> — {{ $item->exam->exam_name }}
                            @else
                                <strong>{{ $item->plan_name }} Access Plan</strong>
                            @endif
                        </td>
                        <td style="text-transform: uppercase; font-size: 11px; color: #64748B; font-weight: 600;">
                            {{ $item->item_type }}
                        </td>
                        <td style="text-align: center; font-weight: 600;">
                            {{ $item->quantity ?? 1 }}
                        </td>
                        <td style="text-align: right; font-weight: 700; font-family: 'JetBrains Mono', monospace;">
                            ${{ number_format($item->price, 2) }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Totals Block -->
        <div class="totals-block">
            <table class="totals-table">
                <tr>
                    <td style="color: #64748B;">Subtotal:</td>
                    <td style="font-weight: 600;">${{ number_format($order->subtotal, 2) }}</td>
                </tr>
                @if($order->discount_amount > 0)
                    <tr>
                        <td style="color: #059669; font-weight: 600;">Discount @if($order->coupon)({{ $order->coupon->code }})@endif:</td>
                        <td style="color: #059669; font-weight: 700;">-${{ number_format($order->discount_amount, 2) }}</td>
                    </tr>
                @endif
                @if($order->tax_amount > 0)
                    <tr>
                        <td style="color: #64748B;">Tax:</td>
                        <td style="font-weight: 600;">${{ number_format($order->tax_amount, 2) }}</td>
                    </tr>
                @endif
                <tr class="total-row">
                    <td>Total Paid:</td>
                    <td>${{ number_format($order->total_amount, 2) }}</td>
                </tr>
                @if($order->refunded_amount > 0)
                    <tr>
                        <td style="color: #7E22CE; font-weight: 600; padding-top: 8px;">Refunded:</td>
                        <td style="color: #7E22CE; font-weight: 700; padding-top: 8px;">-${{ number_format($order->refunded_amount, 2) }}</td>
                    </tr>
                    <tr>
                        <td style="color: #0A1628; font-weight: 800;">Net Balance:</td>
                        <td style="color: #059669; font-weight: 800;">${{ number_format(max(0, $order->total_amount - $order->refunded_amount), 2) }}</td>
                    </tr>
                @endif
            </table>
        </div>

        <!-- Footer -->
        <div class="footer-note">
            <p>Thank you for choosing ExamTopicsBase for your certification preparation.</p>
            <p style="margin-top: 4px;">ExamTopicsBase &bull; All rights reserved &bull; Computer Generated Customer Invoice</p>
        </div>
    </div>

</body>
</html>
