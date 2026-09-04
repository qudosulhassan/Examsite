<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 14px;
            color: #1A1A2E;
            margin: 0;
            padding: 0;
        }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.15);
            background: #fff;
        }
        .invoice-box table {
            width: 100%;
            line-height: inherit;
            text-align: left;
            border-collapse: collapse;
        }
        .invoice-box table td {
            padding: 8px;
            vertical-align: top;
        }
        .invoice-box table tr td:nth-child(2) {
            text-align: right;
        }
        .invoice-box table tr.top td.title {
            font-size: 30px;
            line-height: 30px;
            font-weight: bold;
            color: #0A1628;
        }
        .invoice-box table tr.information td {
            padding-bottom: 40px;
        }
        .invoice-box table tr.heading td {
            background: #0A1628;
            color: #fff;
            border-bottom: 1px solid #ddd;
            font-weight: bold;
            font-size: 12px;
            text-transform: uppercase;
        }
        .invoice-box table tr.details td {
            padding-bottom: 20px;
        }
        .invoice-box table tr.item td {
            border-bottom: 1px solid #eee;
        }
        .invoice-box table tr.item.last td {
            border-bottom: none;
        }
        .invoice-box table tr.total td {
            border-top: 2px solid #eee;
            font-weight: bold;
            font-size: 16px;
        }
        .cyan-text { color: #00D4AA; }
    </style>
</head>
<body>
    <div class="invoice-box">
        <table>
            <tr class="top">
                <td colspan="2">
                    <table>
                        <tr>
                            <td class="title">
                                Exams<span class="cyan-text">Ninja</span>
                            </td>
                            <td>
                                Invoice #: {{ $order->order_number }}<br>
                                Date: {{ $order->created_at->format('F d, Y') }}<br>
                                Status: <strong style="text-transform: uppercase; color: green;">{{ $order->payment_status }}</strong>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="information">
                <td colspan="2">
                    <table>
                        <tr>
                            <td>
                                Exam Topics Base.com Support<br>
                                support@examtopicsbase.com<br>
                                30-Day Guarantee Protected
                            </td>
                            <td>
                                Billing Name: {{ $order->billing_name }}<br>
                                Billing Email: {{ $order->billing_email }}<br>
                                Payment Method: {{ strtoupper($order->payment_method) }}
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
            
            <tr class="heading">
                <td>Item Description</td>
                <td>Price</td>
            </tr>
            
            @foreach($order->items as $item)
                <tr class="item">
                    <td>
                        @if($item->item_type === 'subscription')
                            Subscription Plan: {{ $item->plan_name }}
                        @elseif($item->exam)
                            {{ $item->exam->exam_code }} - {{ $item->exam->exam_name }} ({{ strtoupper($item->item_type) }})
                        @else
                            IT Study Guide Package
                        @endif
                    </td>
                    <td>
                        ${{ number_format($item->price, 2) }}
                    </td>
                </tr>
            @endforeach
            
            <tr class="item last">
                <td>Subtotal</td>
                <td>${{ number_format($order->subtotal, 2) }}</td>
            </tr>
            
            @if($order->discount_amount > 0)
                <tr class="item last" style="color: red;">
                    <td>Discount Applied</td>
                    <td>-${{ number_format($order->discount_amount, 2) }}</td>
                </tr>
            @endif
            
            <tr class="total">
                <td></td>
                <td>Total Paid: ${{ number_format($order->total_amount, 2) }}</td>
            </tr>
        </table>
    </div>
</body>
</html>
