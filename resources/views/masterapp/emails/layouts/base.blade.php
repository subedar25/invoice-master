<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $subject ?? 'Email Notification' }}</title>
    <style>
        body { margin: 0; padding: 0; background-color: #f3f6fb; }
        .wrapper { width: 100%; background-color: #f3f6fb; padding: 24px 0; }
        .container { width: 100%; max-width: 600px; margin: 0 auto; background: #ffffff; border: 1px solid #e6ecf2; border-radius: 10px; overflow: hidden; }
        .header { background: #0b5ed7; color: #ffffff; padding: 24px; text-align: center; }
        .header h1 { margin: 0; font-size: 22px; letter-spacing: 0.2px; }
        .content { padding: 28px 24px; color: #1f2a37; font-size: 15px; line-height: 1.6; }
        .button { display: inline-block; background: #16a34a; color: #ffffff; padding: 12px 20px; border-radius: 6px; text-decoration: none; font-weight: 600; }
        .footer { background: #f8fafc; color: #6b7280; font-size: 12px; text-align: center; padding: 18px 24px; border-top: 1px solid #e6ecf2; }
        .muted { color: #6b7280; font-size: 12px; }
        .divider { height: 1px; background: #e6ecf2; margin: 20px 0; }
    </style>
</head>
<body>
    <div class="wrapper">
        <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
            <tr>
                <td align="center" style="padding: 0 12px;">
                    <table role="presentation" cellspacing="0" cellpadding="0" border="0" class="container">
                        <tr>
                            <td class="header">
                                <h1>{{ $headerTitle ?? ($subject ?? 'Notification') }}</h1>
                                @if(!empty($headerSubtitle))
                                    <div class="muted" style="color:#dbe7ff;">{{ $headerSubtitle }}</div>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="content">
                                @yield('content')
                            </td>
                        </tr>
                        <tr>
                            <td class="footer">
                                <div>{{ $footerText ?? ('Regards, ' . ($appName ?? config('app.name'))) }}</div>
                                <div class="muted">&copy; {{ date('Y') }} {{ $appName ?? config('app.name') }}. All rights reserved.</div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr>
        </table>
    </div>
</body>
</html>
