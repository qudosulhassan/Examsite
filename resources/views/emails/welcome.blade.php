<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to Exam Topics Base</title>
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
            <h2>Welcome, {{ $user->name }}!</h2>
            <p>Thank you for registering at <strong>Exam Topics Base.com</strong>. We are thrilled to help you master your IT certification exams and boost your career.</p>
            <p>Our platform offers verified study guides, printable PDF dumps, and a sophisticated, timed web-based testing engine simulator that replicates real testing conditions (with Practice, Exam, and Review modes).</p>
            
            <p>Ready to kickstart your preparation?</p>
            <div style="text-align: center;">
                <a href="{{ route('vendors.index') }}" class="btn" target="_blank">Browse IT Certifications</a>
            </div>

            <p>Here is what you can do next:</p>
            <ul style="padding-left: 20px; margin: 0 0 20px 0; font-size: 14px; color: #4a5568;">
                <li>Search for specific certifications (Cisco, AWS, CompTIA, Microsoft, etc.)</li>
                <li>Download free sample demo PDFs to review question quality</li>
                <li>Purchase single guides or get full unlimited access via subscription</li>
            </ul>

            <p>If you have any questions or need technical support, our team is always ready to guide you.</p>
            <p>Best regards,<br>The Exam Topics Base Team</p>
        </div>
        <div class="footer">
            <p>Pass Your IT Certification Exam First Attempt. Guaranteed.</p>
            <p>&copy; {{ date('Y') }} Exam Topics Base.com. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
