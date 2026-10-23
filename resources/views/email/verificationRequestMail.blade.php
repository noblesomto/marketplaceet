<!DOCTYPE html>
<html>
<head>
    <title>User Verification Required</title>
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
            background-color: #4CAF50;
            color: white;
            padding: 25px 20px;
            text-align: center;
            border-radius: 5px 5px 0 0;
        }

        .logo {
            max-height: 80px;
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
        <h1>User Verification Required</h1>
    </div>

    <div class="content">
        <p>Hello Admin,</p>
        <p>A user has updated their information and requires verification:</p>

        <div class="details">
            <p><strong>Name:</strong> {{ $details['name'] }}</p>
            <p><strong>Email:</strong> {{ $details['email'] }}</p>
            <p><strong>Document Number:</strong> {{ $details['document_number'] }}</p>
        </div>

        <p>Please log in to the admin panel to review and verify this information.</p>
        <a href="{{ route('admin.login') }}" class="button">Go to Admin Panel</a>
    </div>

    <div class="footer">
        <p>Thank you,</p>
        <p>Your System Team</p>
    </div>
</body>
</html>
