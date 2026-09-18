<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Your Email Address - ExamTopicsBase</title>
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
        .btn-wrapper {
            text-align: center;
            margin: 30px 0;
        }
        .btn {
            display: inline-block;
            background-color: #00D4AA;
            color: #0A1628 !important;
            text-decoration: none;
            padding: 14px 32px;
            border-radius: 6px;
            font-weight: bold;
            font-size: 16px;
            box-shadow: 0 4px 6px rgba(0, 212, 170, 0.2);
        }
        .alt-link {
            font-size: 12px;
            color: #718096;
            word-break: break-all;
            background-color: #f7fafc;
            padding: 12px;
            border-radius: 4px;
            border: 1px solid #edf2f7;
            margin-top: 20px;
        }
        .footer {
            background-color: #F8F9FB;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #718096;
            border-top: 1px solid #e2e8f0;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Exam<span>Topics</span>Base</h1>
        </div>
        <div class="body">
            <h2>Hello, {{ $name }}!</h2>
            <p>Thank you for creating an account with <strong>ExamTopicsBase</strong>. Please click the button below to verify your email address and activate your account:</p>
            
            <div class="btn-wrapper">
                <a href="{{ $verificationUrl }}" class="btn">Verify Email Address</a>
            </div>

            <p>This verification link will expire in 60 minutes.</p>
            <p>If you did not create an account on ExamTopicsBase, no further action is required.</p>

            <div class="alt-link">
                <strong>Having trouble with the button?</strong> Copy and paste the link below into your web browser:<br>
                <a href="{{ $verificationUrl }}" style="color: #00D4AA;">{{ $verificationUrl }}</a>
            </div>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} ExamTopicsBase. All rights reserved.</p>
            <p>https://examtopicsbase.com/</p>
        </div>
    </div>
</body>
</html>