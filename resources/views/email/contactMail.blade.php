<!DOCTYPE html>
<html>
<head>
    <title>Contact Admin</title>
</head>
<body>
    <p>A user with the following details made an inquiry on the website</p>
    <h1>Name: {{ $details['name'] }}</h1>
    <p>Email: {{ $details['email'] }}</p>
    <p>Subject: {{ $details['subject'] }}</p>
    <p>Message: {{ $details['message'] }}</p>
   
    <p>Thank you</p>
</body>
</html>