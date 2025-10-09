<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', config('app.name'))</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333333; max-width: 600px; margin: 0 auto; padding: 20px;">
    
    <div style="background-color: #ffffff; padding: 20px; border-radius: 5px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
        <div style="margin-bottom: 20px;">
            <img src="{{ asset('icons/android-icon-72x72.png') }}" 
                 alt="{{ config('app.name') }}" 
                 style="width: 48px; height: 48px; vertical-align: middle; margin-right: 10px;">
            <span style="font-size: 20px; font-weight: bold; color: #48745D; vertical-align: middle;">{{ config('app.name') }}</span>
        </div>

        @yield('content')

        <hr style="border: none; border-top: 1px solid #eee; margin: 25px 0;">

        <div style="font-size: 14px; color: #666666;">
            <p style="margin-bottom: 10px;">
                Questions? Contact us at: 
                <a href="mailto:{{ config('mail.contact_email') }}" style="color: #007bff;">{{ config('mail.contact_email') }}</a>
            </p>

            <p style="margin-bottom: 10px; font-size: 12px;">
                This email was sent to {{ $user->email ?? '' }}
            </p>
        </div>
    </div>
</body>
</html> 