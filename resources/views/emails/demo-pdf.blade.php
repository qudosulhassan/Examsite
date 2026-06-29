<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your Free Demo Guide</title>
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
            border: 1px border-color #e2e8f0;
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
            <h2>Hi {{ $demoRequest->name }},</h2>
            <p>Thank you for requesting a free sample study guide for the <strong>{{ $exam->exam_code }} - {{ $exam->exam_name }}</strong> certification exam.</p>
            <p>Your download link is ready! Click the button below to download the sample questions directly from our Cloudflare R2 secure storage:</p>
            
            <div style="text-align: center;">
                <a href="{{ $downloadUrl }}" class="btn" target="_blank">Download Free Demo PDF</a>
            </div>

            <p style="font-size: 12px; color: #718096;">Please note: For security reasons, this download link will expire in 24 hours.</p>
            
            <hr style="border: 0; border-top: 1px solid #edf2f7; margin: 30px 0;">
            
            <h3 style="font-size: 14px; margin-top: 0; color: #0A1628;">Ready to Unlock the Full Version?</h3>
            <p>Our premium packages include all practice questions with verified answers, detailed explanations, and 90 days of free updates. You can also upgrade to our online timed testing engine to practice in a realistic exam simulator.</p>
            <p><a href="{{ url('/exams/' . $exam->slug) }}" style="color: #00D4AA; font-weight: bold; text-decoration: none;">Get the Full Guide & Test Engine &rarr;</a></p>
        </div>
        <div class="footer">
            <p>Pass Your IT Certification Exam First Attempt. Guaranteed.</p>
            <p>&copy; {{ date('Y') }} ExamsNinja.com. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
