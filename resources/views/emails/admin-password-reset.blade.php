<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Password Reset</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <div style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 30px; text-align: center; border-radius: 10px 10px 0 0;">
        <h1 style="color: white; margin: 0; font-size: 28px;">{{ config('global.site_name') }}</h1>
        <p style="color: #f0f0f0; margin: 10px 0 0 0;">Admin Password Reset Request</p>
    </div>

    <div style="background: #ffffff; padding: 30px; border: 1px solid #e0e0e0; border-top: none;">
        <h2 style="color: #333; margin-top: 0;">Hello {{ $details['username'] }}!</h2>

        <p>We received a request to reset your admin account password. If you didn't make this request, please ignore this email and your password will remain unchanged.</p>

        <p>To reset your password, click the button below:</p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/admin/reset-password/' . $details['admin_id'] . '/' . $details['token']) }}"
               style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                      color: white;
                      padding: 15px 40px;
                      text-decoration: none;
                      border-radius: 5px;
                      display: inline-block;
                      font-weight: bold;
                      box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                Reset Admin Password
            </a>
        </div>

        <p style="color: #666; font-size: 14px; margin-top: 30px;">
            If the button doesn't work, copy and paste this link into your browser:
        </p>
        <p style="background: #f5f5f5; padding: 10px; border-radius: 5px; word-break: break-all; font-size: 12px; color: #666;">
            {{ url('/admin/reset-password/' . $details['admin_id'] . '/' . $details['token']) }}
        </p>

        <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e0e0e0;">
            <p style="color: #999; font-size: 13px; margin: 5px 0;">
                <strong>Security Note:</strong> This link will expire after being used once. For security reasons, we recommend changing your password immediately after logging in.
            </p>
        </div>
    </div>

    <div style="background: #f9f9f9; padding: 20px; text-align: center; border-radius: 0 0 10px 10px; border: 1px solid #e0e0e0; border-top: none;">
        <p style="color: #666; font-size: 12px; margin: 0;">
            This is an automated message from <strong>{{ config('global.site_name') }}</strong> Admin System
        </p>
        <p style="color: #999; font-size: 11px; margin: 5px 0 0 0;">
            &copy; {{ date('Y') }} {{ config('global.site_name') }}. All rights reserved.
        </p>
    </div>
</body>
</html>
