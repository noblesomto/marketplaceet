<!DOCTYPE html>
<html>
<head>
    <title>Order Canceled – Refund Required</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #d9534f;
            color: white;
            padding: 25px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }
        .content {
            padding: 20px 0;
        }
        .details {
            background: #f9f9f9;
            padding: 15px;
            border-radius: 5px;
            margin: 15px 0;
        }
        .footer {
            margin-top: 20px;
            font-size: 0.9em;
            color: #777;
        }
        .button {
            display: inline-block;
            padding: 10px 15px;
            background: #3498db;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin: 10px 0;
        }
    </style>
</head>
<body>
    <div class="header">
       <h1>Order Canceled – Refund Required</h1>
    </div>

    <div class="content">
        <p>Hello Admin,</p>
        <p>The {{ strtolower($details['canceled_by'] ?? 'seller') }} canceled an order after payment. Please process a refund for the buyer.</p>

        <div class="details">
            <p><strong>Order:</strong> {{ $details['order_code'] }}</p>
            <p><strong>Item:</strong> {{ $details['advert'] }}</p>
            <p><strong>Amount Paid:</strong> Br {{ number_format($details['amount_paid'], 2) }}</p>
            <p><strong>Payment Reference:</strong> {{ $details['reference'] }}</p>
            <br>
            <p><strong>Seller:</strong> {{ $details['seller_name'] }} ({{ $details['seller_email'] }})</p>
            <br>
            <p><strong>Buyer:</strong> {{ $details['buyer_name'] }}</p>
            <p><strong>Buyer Phone:</strong> {{ $details['buyer_phone'] }}</p>
            <p><strong>Buyer Email:</strong> {{ $details['buyer_email'] }}</p>
        </div>

        <p>Please log in to the admin panel to review this order and process the buyer's refund.</p>
        <a href="{{ route('admin.login') }}" class="button">Go to Admin Panel</a>
    </div>

    <div class="footer">
        <p>Thank you,</p>
        <b>{{ config('global.email_title') }}</b>
    </div>
</body>
</html>
