<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Inquiry Received</title>
</head>
<body>
    <h1>New Inquiry Received</h1>
    <p><strong>Name:</strong> {{ $inquiry->name }}</p>
    <p><strong>Email:</strong> {{ $inquiry->email }}</p>
    <p><strong>Message:</strong> {{ $inquiry->message }}</p>
</body>
</html>
