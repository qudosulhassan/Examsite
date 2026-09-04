<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation - Exam Topics Base</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            background-color: #F8F9FB;
            margin: 0;
            padding: 0;
            color: #1A1A2E;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
            border: 1px solid #e2e8f0;
        }
        .header {
            background-color: #0A1628;
            padding: 30px;
            text-align: center;
        }
        .header h1 {
            color: #ffffff;
            margin: 0;
            font-size: 24px;
            font-weight: 700;
        }
        .header span {
            color: #00D4AA;
        }
        .body {
            padding: 40px 30px;
            line-height: 1.6;
            font-size: 15px;
        }
        .body h2 {
            font-size: 18px;
            margin-top: 0;
            color: #0A1628;
        }
        .order-table {
            width: 100%;
            border-collapse: collapse;
            margin: 20px 0;
            font-size: 14px;
        }
        .order-table th {
            text-align: left;
            padding: 10px;
            background-color: #f7fafc;
            border-bottom: 2px solid #edf2f7;
            color: #4a5568;
            font-weight: bold;
        }
        .order-table td {
            padding: 12px 10px;
            border-bottom: 1px solid #edf2f7;
            color: #2d3748;
        }
        .btn {
            display: inline-block;
            background-color: #FF6B35;
            color: #ffffff;
            text-decoration: none;
            padding: 14px 28px;
            border-radius: 6px;
            font-weight: bold;
            margin: 20px 0;
            text-align: center;
        }
        .btn:hover {
            background-color: #e55a26;
        }
        .footer {
            background-color: #f7fafc;
            padding: 20px 30px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #edf2f7;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Exams<span>Ninja</span></h1>
        </div>
        <div class="body">
            <h2>Thank you for your order, {{ $order->user->name }}!</h2>
            <p>We are pleased to confirm that we have received your payment for order <strong>#{{ $order->order_number }}</strong>.</p>
            
            <div style="background-color: #f7fafc; border: 1px solid #edf2f7; padding: 15px; border-radius: 6px; font-size: 13px; color: #4a5568; margin-bottom: 25px;">
                <strong>Order Date:</strong> {{ $order->created_at->format('F d, Y h:i A') }}<br>
                <strong>Payment Method:</strong> {{ strtoupper($order->payment_method) }}<br>
                <strong>Billing Name:</strong> {{ $order->billing_name }}<br>
                <strong>Billing Email:</strong> {{ $order->billing_email }}
            </div>

            <h3 style="font-size: 15px; border-bottom: 1px solid #edf2f7; pb-10; color: #0A1628; margin: 0 0 10px 0;">Purchased Items</h3>
            <table class="order-table">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Type</th>
                        <th style="text-align: right;">Price</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($order->items as $item)
                        <tr>
                            <td>
                                @if($item->exam)
                                    {{ $item->exam->vendor->name }} {{ $item->exam->exam_code }}
                                @else
                                    {{ $item->plan_name }} Plan
                                @endif
                            </td>
                            <td>
                                @if($item->item_type === 'pdf')
                                    PDF Study Guide
                                @elseif($item->item_type === 'engine_single')
                                    Single Exam Simulator
                                @else
                                    Subscription Plan
                                @endif
                            </td>
                            <td style="text-align: right;">${{ number_format($item->price, 2) }}</td>
                        </tr>
                    @endforeach
                    @if($order->discount_amount > 0)
                        <tr>
                            <td colspan="2" style="text-align: right; font-weight: bold; color: #38a169;">Coupon Discount</td>
                            <td style="text-align: right; font-weight: bold; color: #38a169;">-${{ number_format($order->discount_amount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td colspan="2" style="text-align: right; font-weight: bold; border-top: 1.5px solid #0A1628; border-bottom: none;">Total Paid</td>
                        <td style="text-align: right; font-weight: bold; color: #0A1628; border-top: 1.5px solid #0A1628; border-bottom: none;">${{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <p>Your purchased guides and test engine keys are immediately available in your student dashboard:</p>
            <div style="text-align: center;">
                <a href="{{ route('dashboard.index') }}" class="btn" target="_blank">Access My Purchased Materials</a>
            </div>

            <p style="font-size: 13px; color: #718096;">Need an invoice? You can generate and download a PDF invoice for this order from your dashboard under the billing tab.</p>
            <p>Best regards,<br>The Exam Topics Base Billing Team</p>
        </div>
        <div class="footer">
            <p>Pass Your IT Certification Exam First Attempt. Guaranteed.</p>
            <p>&copy; {{ date('Y') }} Exam Topics Base.com. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
