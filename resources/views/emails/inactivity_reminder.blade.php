<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>We Miss You!</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 20px;">
            <img src="/icons/android-icon-72x72.png" 
                 alt="{{ config('app.name') }}" 
                 style="width: 48px; height: 48; vertical-align: middle; margin-right: 10px;">
            <span style="font-size: 20px; font-weight: bold; color: #48745D; vertical-align: middle;">{{ config('app.name') }}</span>
        </div>
        <h1 style="color: #1a492d; margin-bottom: 20px;">Hi {{ $user->name }}!</h1>

        <p style="margin-bottom: 15px;">
            {{ $body }}
        </p>

        <div style="text-align: center; margin: 30px 0;">
            <a href="{{ url('/login') }}" style="display: inline-block; background-color: #48745D; color: #ffffff; padding: 10px 50px; text-decoration: none; border-radius: 100px; font-weight: bold;width: 100%;max-width: 300px;margin: 10px 0;box-sizing: border-box;">
                Login Now
            </a>
        </div>

        <p style="margin-bottom: 15px;">
            If you're having any issues with your account or have questions, we're here to help!
        </p>

        <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">

        <div style="font-size: 14px; color: #666666;">
            <p style="margin-bottom: 10px;">
                Questions? Contact us at: 
                <a href="{{ config('mail.contact_email') }}" style="color: #007bff;">{{ config('mail.contact_email') }}</a>
            </p>

            <p style="margin-bottom: 10px; font-size: 12px;">
                This email was sent to {{ $user->email }}
            </p>
        </div>
    </div>
</body>
</html>