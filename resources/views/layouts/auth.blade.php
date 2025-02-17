<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        @php
            use Illuminate\Support\Facades\URL;
        @endphp
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <link href="{{ URL::asset('main.css') }}" rel="stylesheet">

        <!-- icons -->
        <link rel="icon" type="image/x-icon" href="/icons/favicon.ico">
        <link rel="icon" type="image/png" sizes="16x16" href="/icons/favicon-16x16.png">
        <link rel="icon" type="image/png" sizes="32x32" href="/icons/favicon-32x32.png">
        <link rel="icon" type="image/png" sizes="96x96" href="/icons/favicon-96x96.png">
        <link rel="apple-touch-icon" sizes="57x57" href="/icons/apple-icon-57x57.png">
        <link rel="apple-touch-icon" sizes="60x60" href="/icons/apple-icon-60x60.png">
        <link rel="apple-touch-icon" sizes="72x72" href="/icons/apple-icon-72x72.png">
        <link rel="apple-touch-icon" sizes="76x76" href="/icons/apple-icon-76x76.png">
        <link rel="apple-touch-icon" sizes="114x114" href="/icons/apple-icon-114x114.png">
        <link rel="apple-touch-icon" sizes="120x120" href="/icons/apple-icon-120x120.png">
        <link rel="apple-touch-icon" sizes="144x144" href="/icons/apple-icon-144x144.png">
        <link rel="apple-touch-icon" sizes="152x152" href="/icons/apple-icon-152x152.png">
        <link rel="apple-touch-icon" sizes="180x180" href="/icons/apple-icon-180x180.png">
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#ffffff">
        <meta name="msapplication-config" content="/icons/browserconfig.xml">
        <meta name="msapplication-TileColor" content="#ffffff">
        <meta name="msapplication-TileImage" content="/icons/ms-icon-144x144.png">
    </head>

    <body class="d-flex align-items-center justify-content-center" style="min-height: 100vh;">
        <div class="container">
            <div class="row justify-content-center">
                @yield('content')
            </div>
        </div>
    </body>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    <script>
        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                pauseAllAudio();
            }
        });

        function pauseAllAudio() {
            const audios = document.querySelectorAll('audio');
            audios.forEach(audio => {
                audio.pause();
            });
        }
    </script>
</html>
