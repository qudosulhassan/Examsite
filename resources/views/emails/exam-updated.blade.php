<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Exam Material Updated - Exam Topics Base</title>
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
            <h2>Updated Exam Prep Available</h2>
            <p>Hi {{ $user->name }},</p>
            <p>We are dedicated to keeping our study materials aligned with the latest certification changes. We have recently updated the questions, diagrams, or explanations for: <strong>{{ $exam->exam_code }} - {{ $exam->exam_name }}</strong>.</p>
            
            <p>Since you have active access to this exam, the updated study guide is immediately available for download, and the simulator question database has been refreshed in real-time.</p>

            <div style="text-align: center;">
                <a href="{{ route('dashboard.index') }}" class="btn" target="_blank">Download Latest Study Pack</a>
            </div>

            <p style="font-size: 13px; color: #718096;">Note: PDF guide buyers receive 90 days of free updates from their purchase date. Subscribed simulator students have access to the latest updates indefinitely for the duration of the subscription.</p>
            
            <p>Best of luck with your study preparation!</p>
            <p>Best regards,<br>The Exam Topics Base Product Team</p>
        </div>
        <div class="footer">
            <p>Pass Your IT Certification Exam First Attempt. Guaranteed.</p>
            <p>&copy; {{ date('Y') }} Exam Topics Base.com. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
