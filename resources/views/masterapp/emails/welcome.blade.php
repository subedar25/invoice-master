<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to {{ $appName }}!</title>
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
            background-color: #007bff; /* A primary color */
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
            background-color: #28a745; /* A success/green color */
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
            <h1>Welcome to {{ $appName }}!</h1>
        </div>

        <!-- Email Content -->
        <div class="content">
            <p>Hi {{ $userName }},</p>
            
            <p>
                Thank you for joining us! We're excited to have you on board. Your account has been successfully created, and you're all set to get started.
            </p>

            <p>
                Click the button below to access your dashboard and explore all the features we have to offer.
            </p>


            <a href="{{ route('masterapp.dashboard') }}" class="cta-button">Go to Dashboard</a>


        </div>

        <!-- Email Footer -->
        <div class="footer">
            <p>If you have any questions, feel free to reply to this email. We're here to help!</p>
            <p>&copy; {{ date('Y') }} {{ $appName }}. All rights reserved.</p>
        </div>
    </div>

</body>
</html>