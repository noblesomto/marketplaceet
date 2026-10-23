@php
    $tone  = $details['tone'] ?? 'green';
    $color = ['green' => '#326916', 'red' => '#c0392b', 'amber' => '#b7791f', 'blue' => '#2b6cb0'][$tone] ?? '#326916';
@endphp
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $details['subject'] }}</title>
</head>
<body style="margin:0;padding:0;background:#f4f5f7;font-family:Arial,Helvetica,sans-serif;color:#333;line-height:1.6;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#f4f5f7;padding:24px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;background:#ffffff;border-radius:8px;overflow:hidden;">
                    <tr>
                        <td style="background:{{ $color }};color:#ffffff;padding:22px 24px;text-align:center;">
                            <h1 style="margin:0;font-size:20px;">{{ $details['heading'] ?? $details['subject'] }}</h1>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:24px;">
                            <p style="margin:0 0 14px;">{{ $details['greeting'] ?? 'Hello,' }}</p>
                            @foreach(($details['lines'] ?? []) as $line)
                                <p style="margin:0 0 12px;">{{ $line }}</p>
                            @endforeach

                            @if(!empty($details['cta_url']))
                                <p style="margin:22px 0 8px;text-align:center;">
                                    <a href="{{ $details['cta_url'] }}" style="display:inline-block;background:{{ $color }};color:#ffffff;text-decoration:none;padding:11px 22px;border-radius:6px;font-weight:bold;">
                                        {{ $details['cta_label'] ?? 'View order' }}
                                    </a>
                                </p>
                            @endif

                            <p style="margin:22px 0 0;">Best regards,<br><b>The {{ config('global.site_name') }} Team</b></p>
                        </td>
                    </tr>
                    <tr>
                        <td style="padding:14px 24px;background:#fafafa;color:#888;font-size:12px;text-align:center;">
                            Need help? <a href="https://wa.me/{{ preg_replace('/\D/', '', config('global.site_phone')) }}" style="color:#326916;">Contact us on WhatsApp</a><br>
                            © {{ date('Y') }} {{ config('global.email_title') }}. All rights reserved.
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
