<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subscription Renewed - ExamsNinja</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Exams<span>Ninja</span></h1>
        </div>
        <div class="body">
            <h2>Your Simulator Access Has Been Extended!</h2>
            <p>Hi {{ $subscription->user->name }},</p>
            <p>We are glad to inform you that your subscription to the <strong>{{ ucfirst($subscription->plan_name) }} Plan</strong> has been successfully renewed.</p>
            <p>Your account continues to have full premium access to our timed web simulator and exam database. We hope you are making great progress towards your certification goal!</p>
            
            <div style="background-color: #f7fafc; border: 1px solid #edf2f7; padding: 15px; border-radius: 6px; font-size: 13px; color: #4a5568; margin-bottom: 25px;">
                <strong>Plan Name:</strong> {{ ucfirst($subscription->plan_name) }} Plan<br>
                <strong>Renewal Period:</strong> {{ $subscription->current_period_start->format('F d, Y') }} - {{ $subscription->current_period_end->format('F d, Y') }}<br>
                <strong>Charged Amount:</strong> ${{ number_format($subscription->amount, 2) }} / {{ $subscription->billing_cycle === 'annual' ? 'year' : 'month' }}
            </div>

            <div style="text-align: center;">
                <a href="{{ route('dashboard.test-engine') }}" class="btn" target="_blank">Resume timed practice sessions</a>
            </div>

            <p>Thank you for choosing ExamsNinja as your certification partner.</p>
            <p>Best regards,<br>The ExamsNinja Billing Team</p>
        </div>
        <div class="footer">
            <p>Pass Your IT Certification Exam First Attempt. Guaranteed.</p>
            <p>&copy; {{ date('Y') }} ExamsNinja.com. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
