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

    @media screen and (max-width: 400px) {
        .outer-padding { padding-left: 15px !important; padding-right: 15px !important; }
        .content { padding: 25px 20px !important; }
        .header { padding: 25px 20px !important; font-size: 18px !important; }
        .button { display: block !important; width: 100% !important; text-align: center !important; padding: 16px !important; }
        .box { padding: 18px !important; }
        .footer { padding: 25px 20px !important; }
    }
</style>
</head>

<body style="background-color:#f4f4f4; width:100%; margin:0; padding:0;">

<table width="100%" cellpadding="0" cellspacing="0">
<tr>
<td align="center" class="outer-padding" style="padding:20px 15px;">

<table class="container" width="100%" cellpadding="0" cellspacing="0" style="max-width:600px;">

    <tr>
        <td class="header">
            Your advert is live again
        </td>
    </tr>

    <tr>
        <td class="content">
            Hi {{ $details['name'] }},<br><br>

            Good news — we reviewed <strong>"{{ $details['ad_title'] }}"</strong> and it's back live and visible to buyers.

            <div class="box">
                <a href="{{ $details['ad_link'] }}" class="button" target="_blank">
                    View Your Advert
                </a>
            </div>

            Thanks for making the changes we asked for.

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
