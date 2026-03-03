<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Your Password - {{ $appName }}</title>
    <style>
        /* Basic styles for better email client compatibility */
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            background-color: #f4f4f9;
            margin: 0;
            padding: 20px;
            color: #333;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        .header {
            background-color: #dc3545; /* Red color for reset */
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .header h1 {
            margin: 0;
            font-size: 28px;
            font-weight: 600;
        }
        .content {
            padding: 40px 20px;
            text-align: center;
        }
        .content p {
            font-size: 16px;
            line-height: 1.7;
            margin-bottom: 25px;
        }
        .cta-button {
            display: inline-block;
            background-color: #dc3545; /* Red color */
            color: #ffffff;
            padding: 12px 30px;
            border-radius: 5px;
            text-decoration: none;
            font-weight: bold;
            font-size: 16px;
            margin-top: 10px;
        }
        .footer {
            background-color: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 14px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
    </style>
</head>
<body>

    <div class="container">
        <!-- Email Header -->
        <div class="header">
            <h1>Reset Your Password</h1>
        </div>

        <!-- Email Content -->
        <div class="content">
            <p>Hi {{ $userName }},</p>

            <p>
                We received a request to reset your password for your account on {{ $appName }}.
                If you made this request, click the button below to reset your password.
            </p>

            <p>
                This link will expire in 60 minutes for security reasons.
            </p>

            {{-- <a href="{{ $resetUrl }}" class="cta-button">Reset Password</a> --}}
            <a href="{{ $resetUrl }}"
                style="
                        display:inline-block;
                        background-color:#dc3545;
                        color:#ffffff;
                        padding:12px 30px;
                        border-radius:5px;
                        text-decoration:none;
                        font-weight:bold;
                        font-size:16px;
                ">
                Reset Password
            </a>
            <p style="font-size:14px;color:#6c757d;">
                If the button doesn’t work, copy and paste this link into your browser:
                <br>
                <a href="{{ $resetUrl }}">{{ $resetUrl }}</a>
            </p>
            <p>
                If you did not request a password reset, please ignore this email. Your password will remain unchanged.
            </p>

        </div>

        <!-- Email Footer -->
        <div class="footer">
            <p>If you have any questions, feel free to reply to this email. We're here to help!</p>
            <p>&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
        </div>
    </div>

</body>
</html>
