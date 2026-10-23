<!DOCTYPE html>
<html>
<head>
    <title>Unmatched Flutterwave Payment</title>
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
            background-color: #e74c3c;
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
    </style>
</head>
<body>
    <div class="header">
       <h1>Paid Transaction With No Matching Record</h1>
    </div>

    <div class="content">
        <p>Hello Admin,</p>
        <p>Flutterwave confirmed a <strong>successful</strong> charge, but the reference does not match any Boost or Buy-Direct payment record in the database. The customer has been charged but has not received the service they paid for.</p>

        <div class="details">
            <p><strong>Reference:</strong> {{ $details['reference'] }}</p>
            <p><strong>Flutterwave Transaction ID:</strong> {{ $details['transaction_id'] }}</p>
            <p><strong>Amount:</strong> Br {{ number_format($details['amount'], 2) }}</p>
            <p><strong>Customer Email:</strong> {{ $details['customer_email'] }}</p>
            <p><strong>Paid At:</strong> {{ $details['paid_at'] }}</p>
        </div>

        <p>This usually means the payment was initiated outside the normal app flow (e.g. directly from a client without calling the backend first), so there is no local record to link it to. Please investigate the customer's account and manually credit them for the correct advert/boost.</p>
    </div>

    <div class="footer">
        <p>Thank you,</p>
        <b>{{ config('global.email_title') }}</b>
    </div>
</body>
</html>
