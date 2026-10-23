<!DOCTYPE html>
<html>
<head>
    <title>
        {{ $details['type'] === 'Price Update' ? 'Marketplace Ethiopia – Price Updated' : 'Marketplace Ethiopia – New Ad Posted' }}
    </title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <style type="text/css">
        /* Base Styles */
        body {
            font-family: 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333333;
            margin: 0;
            padding: 0;
            -webkit-text-size-adjust: 100%;
            -ms-text-size-adjust: 100%;
            width: 100% !important;
        }

        .container { max-width: 600px; margin: 0 auto; }
        .header {
            background-color: #4CAF50;
            color: white;
            padding: 25px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 25px;
            background-color: #f9f9f9;
            border-left: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
        }
        .section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        .section:last-child { border-bottom: none; }
        h1 { color: white; margin: 0 0 10px 0; font-size: 24px; }
        h2 { color: #2E7D32; margin: 0 0 15px 0; font-size: 20px; }
        p { margin: 0 0 10px 0; font-size: 14px; }
        .footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #777;
            background-color: #f1f1f1;
            border-radius: 0 0 5px 5px;
            border: 1px solid #e0e0e0;
            border-top: none;
        }
        @media only screen and (max-width: 480px) {
            .header, .content, .footer { padding: 15px !important; }
            h1 { font-size: 20px !important; }
            h2 { font-size: 18px !important; }
            p { margin-bottom: 12px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>
                {{ $details['type'] === 'Price Update' ? 'Price Update' : 'New Ad Posted' }}
            </h1>
        </div>

        <div class="content">
            <div class="section">
                <h2>Hello {{ $details['name'] }},</h2>

                @if($details['type'] === 'New Ad')
                    <p>
                        {{ $details['sellerName'] ?? 'A seller' }} posted a new Ad:
                        <b>
                            <a href="{{ url($details['state_slug'].'/'.$details['title_slug'].'/'.$details['ad_id']) }}" target="_blank">
                                {{ $details['advert'] }}
                            </a>
                        </b>
                    </p>
                @elseif($details['type'] === 'Price Update')
                    <p>
                        {{ $details['sellerName'] ?? 'A seller' }} updated the price of:
                        <b>
                            <a href="{{ url($details['state_slug'].'/'.$details['title_slug'].'/'.$details['ad_id']) }}" target="_blank">
                                {{ $details['advert'] }}
                            </a>
                        </b>
                    </p>
                @endif
            </div>

            <div class="section">
                <p>You’re receiving this email because you’re following this seller on our platform. If you no longer wish to receive notifications from this seller, you can manage your follow preferences or unfollow them at any time in your account settings.</p>
                <br>

                <br>
                <p>Best regards,</p>
                <p><b>The Marketplace Ethiopia Team</b></p>
            </div>
        </div>

        <div class="footer">
            <p>© {{ date('Y') }} {{ config('global.email_title') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
