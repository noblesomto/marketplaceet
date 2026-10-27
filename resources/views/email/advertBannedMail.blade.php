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
        background-color: #dc3545;
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
        background: #fdecec;
        border: 1px solid #f5c2c2;
        border-radius: 6px;
        padding: 20px 25px;
        margin: 25px 0;
    }

    .box strong { color: #b42318; }

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
        .box { padding: 16px !important; }
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
            @if($details['is_resubmission_reject'])
                Your resubmission wasn't approved
            @else
                Your advert has been disabled
            @endif
        </td>
    </tr>

    <tr>
        <td class="content">
            Hi {{ $details['name'] }},<br><br>

            @if($details['is_resubmission_reject'])
                We reviewed your resubmitted advert <strong>"{{ $details['ad_title'] }}"</strong> and it still doesn't meet our guidelines, so it remains hidden from buyers.
            @else
                Your advert <strong>"{{ $details['ad_title'] }}"</strong> has been disabled and is no longer visible to buyers.
            @endif

            <div class="box">
                <strong>Reason:</strong> {{ $details['reason_category'] }}
                @if(!empty($details['reason_note']))
                    <br><br>{{ $details['reason_note'] }}
                @endif
            </div>

            @if(!empty($details['edit_images_url']))
                Update your photos, then resubmit the advert for review from your "My Ads" page — our team will take another look before it goes live again.

                {{-- Inline-styled (no .button class) so the mobile full-width rule can't stretch it past the card --}}
                <table width="100%" cellpadding="0" cellspacing="0" style="margin:25px 0 5px;">
                    <tr>
                        <td align="center">
                            <table cellpadding="0" cellspacing="0" style="margin:0 auto;">
                                <tr>
                                    <td align="center" style="background-color:#4CAF50; border-radius:6px;">
                                        <a href="{{ $details['edit_images_url'] }}" target="_blank" style="display:inline-block; padding:12px 24px; color:#ffffff; text-decoration:none; font-weight:bold; font-size:15px; font-family:Arial, sans-serif;">Update Ad Images</a>
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            @else
                Once you've fixed the issue, you can resubmit it for review from your "My Ads" page — our team will take another look before it goes live again.
            @endif

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
