<!DOCTYPE html>
<html>
<head>
    <title>Contact Admin</title>
</head>
<body>
    <p>A user with the following details made an inquiry about a property</p>
    <h1>Property: {{ $details['property'] }}</h1><br>
    <p>Price: {{ $details['price'] }}</p> 
    <hr>
    <p>Email: {{ $details['email'] }}</p>
    <p>Phone: {{ $details['phone'] }}</p>
    <p>Question: {{ $details['question'] }}</p>
    
    
   
    <p>Thank you</p>
</body>
</html>