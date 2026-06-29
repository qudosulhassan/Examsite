<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Failed - ExamsNinja</title>
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
        .alert-box {
            background-color: #fffaf0;
            border: 1px solid #fbd38d;
            border-left: 4px solid #dd6b20;
            padding: 15px;
            border-radius: 4px;
            font-size: 14px;
            color: #7b341e;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Exams<span>Ninja</span></h1>
        </div>
        <div class="body">
            <h2>Payment Renewal Failed</h2>
            <p>Hi {{ $subscription->user->name }},</p>
            <p>This is to inform you that we attempted to process your subscription renewal payment for the <strong>{{ ucfirst($subscription->plan_name) }} Plan</strong>, but the charge was declined by your bank or payment issuer.</p>
            
            <div class="alert-box">
                <strong>Attention Needed:</strong> Your Test Engine Simulator access has been temporarily suspended. We will automatically retry this charge within the next 48 hours.
            </div>

            <div style="background-color: #f7fafc; border: 1px solid #edf2f7; padding: 15px; border-radius: 6px; font-size: 13px; color: #4a5568; margin-bottom: 25px;">
                <strong>Plan Name:</strong> {{ ucfirst($subscription->plan_name) }} Plan<br>
                <strong>Pending Amount:</strong> ${{ number_format($subscription->amount, 2) }}
            </div>

            <p>To avoid service disruption or restore your access immediately, please review and update your payment details or card parameters inside your dashboard billing profile:</p>
            
            <div style="text-align: center;">
                <a href="{{ route('dashboard.orders') }}" class="btn" target="_blank">Update Payment Details</a>
            </div>

            <p>If you need assistance, please feel free to reach out to our billing support desk.</p>
            <p>Best regards,<br>The ExamsNinja Support Team</p>
        </div>
        <div class="footer">
            <p>Pass Your IT Certification Exam First Attempt. Guaranteed.</p>
            <p>&copy; {{ date('Y') }} ExamsNinja.com. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
