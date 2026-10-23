<!DOCTYPE html>
<html>
<head>
    <title></title>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style type="text/css">
        /* Base Styles */
        body, table, td, a {
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            font-family: 'Poppins', Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        img {
            border: 0;
            height: auto;
            line-height: 100%;
            max-width: 100%;
            outline: none;
            text-decoration: none;
        }
        
        /* iOS Blue Links Fix */
        a[x-apple-data-detectors] {
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }
        
        /* Main Styles */
        .email-container {
            max-width: 600px;
            margin: 0 auto;
        }
        
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 25px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        
        .logo {
            max-height: 80px;
        }
        
        .content-box {
            background-color: #ffffff;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            padding: 40px;
        }
        
        .greeting {
            color: #172541;
            font-size: 28px;
            font-weight: 600;
            margin-bottom: 20px;
            text-align: center;
        }
        
        .message {
            color: #555555;
            font-size: 16px;
            line-height: 1.6;
            margin-bottom: 30px;
        }
        
        .otp-container {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 20px;
            margin: 30px 0;
            text-align: center;
        }
        
        .otp-code {
            color: #1d4b00;
            font-size: 32px;
            font-weight: 600;
            letter-spacing: 3px;
            margin: 15px 0;
        }
        
        .meta-info {
            color: #777777;
            font-size: 14px;
            margin-top: 10px;
        }
        
        .footer {
            background-color: #f8f9fa;
            border-radius: 8px;
            padding: 30px;
            margin-top: 30px;
            text-align: center;
        }
        
        .help-link {
            color: #172541;
            font-weight: 500;
            text-decoration: none;
        }
        
        .help-link:hover {
            text-decoration: underline;
        }
        
        .signature {
            color: #555555;
            font-size: 16px;
            margin-top: 30px;
        }
        /* Typography */
        h1 {
            color: white;
            margin: 0 0 10px 0;
            font-size: 24px;
        }

        h2 {
            color: #2E7D32;
            margin: 0 0 15px 0;
            font-size: 20px;
        }
        h3 {
            color: #FF0000;
            margin: 0 0 12px 0;
            font-size: 18px;
        }

        h4 {
            color: #4CAF50;
            margin: 0 0 12px 0;
            font-size: 16px;
        }
        
        /* Responsive Styles */
        @media screen and (max-width: 600px) {
            .content-box {
                padding: 30px 20px;
            }
            
            .greeting {
                font-size: 24px;
            }
            
            .otp-code {
                font-size: 28px;
            }
        }
    </style>
</head>

<body style="background-color: #f4f4f4; margin: 0; padding: 0;">
    <!-- Hidden Preheader Text -->
    <div style="display: none; max-height: 0; overflow: hidden;">
        Dear {{ $details['name'] }}, here is your one time password for {{ config('global.email_title') }}
    </div>

    <!-- Email Container -->
    <table class="email-container" align="center" border="0" cellpadding="0" cellspacing="0">
        <!-- Header -->
        <tr>
            <td class="header">
                <h1>Let’s get you signed in</h1>
            </td>
        </tr>
        
        <!-- Main Content -->
        <tr>
            <td style="">
                <table class="content-box" align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td style="padding: 40px 30px;">
                           
                            <p class="message">
                                Hi {{ $details['name'] }},<br><br>
                                Here is your {{ config('global.email_title') }} One Time Password (OTP):
                            </p>
                            
                            <div class="otp-container">
                                <div style="font-size: 14px; color: #777;">Your verification code</div>
                                <div class="otp-code">{{ $details['otp'] }}</div>
                                <div class="meta-info">
                                    IP Address: {{ $details['ip'] }}<br>
                                    Sent on: {{ now()->format('F j, Y \a\t g:i A') }}<br>
                                    Expires in 15 Minutes
                                </div>
                            </div>
                            
                            <h3 class="">Important:</h3>
                            <div class="message">
                                <p>
                                    Please note that we do not offer rewards for clicking links or completing tasks. Any such request is a scam and not from us.
                                </p><br>
                               <p>
                                    If you have any questions, please <a href="https://wa.me/{{ preg_replace('/\D/', '', config('global.site_phone')) }}" target="_blank" class="text-gray-700  hover:text-black px-3 py-2 rounded-md text-sm font-medium">contact us on WhatsApp</a>. Our team is always available to assist you.
                               </p>
                            </div>


                            
                            <p class="signature">
                                Cheers,<br>
                                The {{ config('global.email_title') }} Team
                            </p>
                        </td>
                    </tr>
                </table>
                
                <!-- Footer -->
                <table class="footer" align="center" border="0" cellpadding="0" cellspacing="0">
                    <tr>
                        <td>
                            <h2 style="font-size: 18px; color: #172541; margin-bottom: 15px;">Need more help?</h2>
                            <p style="margin: 0;">
                                <a href="{{ url('/contact-us') }}" class="help-link" target="_blank">
                                    We're here to help you out
                                </a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
