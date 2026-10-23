<!DOCTYPE html>
<html>
<head>
    <title>Marketplace Ethiopia – Order Confirmation & Purchase Details</title>
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
           <h1>Your Order Summary</h1>
        </div>
        
        <div class="content">

            <div class="section">
                <p>Hello {{ $details['buyer'] }},</p>
                <p>Thank you for your purchase on {{ config('global.site_name') }}. Your payment is held securely with us and is only released to the seller after you confirm you have received your item.</p>
            </div>

            <div class="section">
                <h4>Order {{ $details['order_code'] }}</h4>
                <p><span class="label">Product Name:</span> {{ $details['advert'] }}</p>
                <p><span class="label">Item price:</span> Br {{ number_format($details['item_price'], 2) }}</p>
                <p><span class="label">Service fee:</span> Br {{ number_format($details['commission'], 2) }}</p>
                <p><span class="label">Total paid:</span> Br {{ number_format($details['amount_paid'], 2) }}</p>
            </div>

            <div class="section">
                <h4>Delivery</h4>
                <p><span class="label">Location:</span> {{ $details['location'] }}</p>
                <p><span class="label">Seller:</span> {{ $details['seller'] }}</p>
                <p>The seller will contact you to arrange delivery and must ship by <b>{{ $details['ship_by'] }}</b>, otherwise your order is canceled and fully refunded.</p>
            </div>

            <div class="section">
                <h4>What happens next</h4>
                <p>Once you receive the item, open your order and tap <b>Confirm received</b>. If something is wrong, tap <b>Report a problem</b> and we will hold the payment while we help. If you take no action, the payment is released to the seller {{ $details['release_days'] }} days after the item is shipped.</p>
                <p style="text-align:center;margin-top:16px;"><a href="{{ $details['buyer_url'] }}" style="display:inline-block;background:#326916;color:#fff;padding:10px 20px;border-radius:6px;text-decoration:none;font-weight:bold;">View order</a></p>
                <br>
                <p>Best regards,</p>
                <p><b>The {{ config('global.site_name') }} Team</b></p>
            </div>
        </div>
        <div class="footer">

            <p>© {{ date('Y') }} {{ config('global.email_title') }}. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
