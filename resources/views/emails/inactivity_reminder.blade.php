<!DOCTYPE html>
<html>
<head>
    <title>We Miss You!</title>
</head>
<body>
    <h1>Hello {{ $user->name }},</h1>
    <p>We noticed that you haven't been active on our app for a while. We miss you!</p>
    <p>Best regards,<br>{{ config('app.name') }}</p>
</body>
</html>
