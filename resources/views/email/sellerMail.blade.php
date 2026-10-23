<!DOCTYPE html>
<html>
<head>
    <title>Marketplace Ethiopia –  Your Item Has Been Sold – Time to Ship!</title>
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
        
        /* Main Container */
        .container {
            max-width: 600px;
            width: 100%;
            margin: 0 auto;
        }
        
        /* Header */
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
        
        /* Content Area */
        .content {
            padding: 25px;
            background-color: #f9f9f9;
            border-left: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
        }
        
        /* Sections */
        .section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
         .shipping-code {
            font-family: monospace;
            font-weight: bold;
            font-size: 20px;
            color: #2E7D32;
        }

        .note {
            font-style: italic;
            color: #7f8c8d;
            margin-top: 20px;
            padding: 10px;
            background-color: #f5f5f5;
            border-left: 3px solid #3498db;
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
        
        h4 {
            color: #4CAF50;
            margin: 0 0 12px 0;
            font-size: 16px;
        }
        
        p {
            margin: 0 0 10px 0;
            font-size: 14px;
        }
        
        /* Label-Value Pairs */
        .label {
            font-weight: bold;
            color: #555;
            display: inline-block;
            width: 80px;
        }
        
        /* Footer */
        .footer {
            text-align: center;
            padding: 15px;
            font-size: 12px;
            color: #777777;
            background-color: #f1f1f1;
            border-radius: 0 0 5px 5px;
            border-left: 1px solid #e0e0e0;
            border-right: 1px solid #e0e0e0;
            border-bottom: 1px solid #e0e0e0;
        }
        
        /* Responsive Adjustments */
        @media only screen and (max-width: 480px) {
            .container {
                width: 100% !important;
            }
            
            .header, .content, .footer {
                padding: 15px !important;
            }
            
            h1 {
                font-size: 20px !important;
            }
            
            h2 {
                font-size: 18px !important;
            }
            
            .label {
                display: block;
                width: auto;
                margin-bottom: 2px;
            }
            
            p {
                margin-bottom: 12px;
            }

        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>New Sale Notification</h1>
        </div>
        
        <div class="content">
            <div class="section">
                <h2>Hello {{ $details['seller'] }},</h2>
                <p>Great news — a buyer has paid for your item on <b>{{ config('global.site_name') }}</b> using Buy Direct. We are holding the payment securely and will release it to you once the buyer confirms they have received the item.</p>
            </div>

            <div class="section">
                <h2>Buyer Information</h2>
                <p><span class="label">Name:</span> {{ $details['buyer'] }}</p>
                <p><span class="label">Phone:</span> {{ $details['phone'] }}</p>
                <p><span class="label">Delivery location:</span> {{ $details['location'] }}</p>
            </div>

            <div class="section">
                <h4>Item Sold</h4>
                <p><span class="label">Product Name:</span> {{ $details['advert'] }}</p>
                <p><span class="label">Order:</span> {{ $details['order_code'] }}</p>
                <p><span class="label">You will receive:</span> Br {{ number_format($details['item_price'], 2) }}</p>
            </div>

            <div class="section">
                <h4>It’s Time to Ship</h4>
                <ol class="shipping-steps">
                    <li>Contact the buyer on the phone number above to agree on delivery or pickup.</li>
                    <li>Package the item well and hand it over or send it to the buyer.</li>
                    <li>Open your order page and tap <b>Mark as shipped</b>.</li>
                </ol>
                <p class="note">Please ship by <b>{{ $details['ship_by'] }}</b>. If the order is not marked as shipped by then, it is canceled automatically and the buyer is refunded.</p>
                <p style="text-align:center;margin-top:16px;"><a href="{{ $details['seller_url'] }}" style="display:inline-block;background:#326916;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">Open order</a></p>
            </div>

            <div class="section">
                <h4>When do I get paid?</h4>
                <p>As soon as the buyer confirms receipt. If the buyer doesn’t respond, the payment is released automatically {{ $details['release_days'] }} days after you mark the order as shipped (unless they report a problem).</p>
                <p>Make sure your bank details are up to date in your profile so we can pay you.</p>
                <br>
                <p>Best regards,</p>
                <p><b>The {{ config('global.site_name') }} Team</b></p>
            </div>
        </div>
        <div class="footer">
            <p>Thank you for using our platform!</p>
            <p>© {{ date('Y') }} {{ config('global.email_title') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
