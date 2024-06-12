<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link href="{{ URL::asset('main.css') }}" rel="stylesheet">
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="container">
            <div class="row justify-content-center">
                @yield('content')
            </div>
        </div>
    </body>
</html>
