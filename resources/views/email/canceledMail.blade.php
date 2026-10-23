<!DOCTYPE html>
<html>
<head>
    <title>Marketplace Ethiopia – Your Order Has Been Canceled</title>
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

            <h1>Your Order Has Been Canceled</h1>
        </div>
        
        <div class="content">
       
            <div class="section">
                <h2>Hello {{ $details['buyer'] }},</h2>
            <p>We’re sorry to inform you that your order for {{ $details['advert'] }} has been canceled.</p>
            <p>We regret any inconvenience caused by this.</p>
            </div>

            <div class="section">
                <h4>Shipping Details:</h4>
                <p><span class="label">Item:</span> {{ $details['advert'] }}</p>
                <p><span class="label">Shipping Provider:</span> GIG Logistics (GIGL)</p>
            </div>
            

            <div class="section">
                <p>If you have any questions or need further assistance, please don’t hesitate to contact our support team.</p>
                <br>
                <p>Thank you for choosing <b>Marketplace Ethiopia.</b> We appreciate your trust and look forward to serving you again.</p>
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
