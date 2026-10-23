<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge" />

<style type="text/css">
    body, table, td, a {
        -webkit-text-size-adjust: 100%;
        -ms-text-size-adjust: 100%;
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;
    }

    table { border-collapse: collapse; }

    img {
        border: 0;
        height: auto;
        max-width: 100%;
        display: block;
    }

    .container {
        max-width: 600px;
        margin: 0 auto;
    }

    .header {
        background-color: #4CAF50;
        color: #ffffff;
        text-align: center;
        padding: 30px 25px;
        font-size: 22px;
        font-weight: bold;
    }

    .content {
        background-color: #ffffff;
        padding: 40px 35px;
        font-size: 16px;
        line-height: 1.6;
        color: #555555;
    }

    .button {
        background-color: #4CAF50;
        color: #ffffff !important;
        text-decoration: none;
        padding: 14px 28px;
        border-radius: 6px;
        display: inline-block;
        font-weight: bold;
        font-size: 16px;
    }

    .box {
        background: #f8f9fa;
        padding: 25px;
        text-align: center;
        margin: 30px 0;
    }

    .footer {
        background: #f8f9fa;
        padding: 30px 25px;
        text-align: center;
        font-size: 14px;
        color: #777777;
    }

    h3 {
        color: #ff0000;
        margin: 25px 0 10px 0;
    }

    /* SMALL DEVICE FIX */
    @media screen and (max-width: 400px) {

        .outer-padding {
            padding-left: 15px !important;
            padding-right: 15px !important;
        }

        .content {
            padding: 25px 20px !important;
        }

        .header {
            padding: 25px 20px !important;
            font-size: 18px !important;
        }

        .button {
            display: block !important;
            width: 100% !important;
            text-align: center !important;
            padding: 16px !important;
        }

        .box {
            padding: 18px !important;
        }

        .footer {
            padding: 25px 20px !important;
        }
    }
</style>
</head>

<body style="background-color:#f4f4f4; width:100%; margin:0; padding:0;">

<!-- OUTER WRAPPER (Controls Side Padding Safely) -->
<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center" class="outer-padding" style="padding:20px 15px;">

<table class="container" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">

    <!-- HEADER -->
    <tr>
        <td class="header">
            Welcome to {{ config('global.email_title') }}
        </td>
    </tr>

    <!-- CONTENT -->
    <tr>
        <td class="content">
            Hi {{ $details['name'] }},<br><br>

            We're excited to have you onboard. Please confirm your account by clicking the button below.

            <div class="box">
                <a href="{{ url('/verifyaccount/'.$details['user_id'].'/'.$details['token']) }}"
                   class="button" target="_blank">
                    Confirm Account
                </a>

                <div style="margin-top:20px;font-size:13px;word-break:break-all;">
                    If the button doesn’t work, copy and paste this link:<br><br>
                    <a href="{{ url('/verifyaccount/'.$details['user_id'].'/'.$details['token']) }}"
                       style="color:#4CAF50;">
                        {{ url('/verifyaccount/'.$details['user_id'].'/'.$details['token']) }}
                    </a>
                </div>
            </div>

            <h3>Important:</h3>
            We do not offer rewards for clicking links or completing tasks. Any such request is a scam.

            <br><br>

            Need help?
            <a href="https://wa.me/{{ preg_replace('/\D/', '', config('global.site_phone')) }}" target="_blank" style="color:#4CAF50;">
                Contact us on WhatsApp
            </a>

            <br><br>

            Cheers,<br>
            The {{ config('global.email_title') }} Team
        </td>
    </tr>

    <!-- FOOTER -->
    <tr>
        <td class="footer">
            <strong>Need more help?</strong><br><br>
            <a href="{{ url('/contact-us') }}" target="_blank" style="color:#172541;text-decoration:none;">
                We're here to help you
            </a>
        </td>
    </tr>

</table>

</td>
</tr>
</table>

</body>
</html>
