@php
    use Illuminate\Support\Facades\URL;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Request;
@endphp

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title')</title>

<!-- css -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.2/themes/base/jquery-ui.css">
<link href="{{ asset('css/main.css') }}" rel="stylesheet">

<!-- icons -->
<link rel="icon" type="image/x-icon" href="{{ Storage::url('icons/favicon.ico')}}">
<link rel="icon" type="image/png" sizes="16x16" href="{{ Storage::url('icons/favicon-16x16.png') }}">
<link rel="icon" type="image/png" sizes="32x32" href="{{ Storage::url('icons/favicon-32x32.png') }}">
<link rel="icon" type="image/png" sizes="96x96" href="{{ Storage::url('icons/favicon-96x96.png') }}">
<link rel="apple-touch-icon" sizes="57x57" href="{{ Storage::url('icons/apple-icon-57x57.png') }}">
<link rel="apple-touch-icon" sizes="60x60" href="{{ Storage::url('icons/apple-icon-60x60.png') }}">
<link rel="apple-touch-icon" sizes="72x72" href="{{ Storage::url('icons/apple-icon-72x72.png') }}">
<link rel="apple-touch-icon" sizes="76x76" href="{{ Storage::url('icons/apple-icon-76x76.png') }}">
<link rel="apple-touch-icon" sizes="114x114" href="{{ Storage::url('icons/apple-icon-114x114.png') }}">
<link rel="apple-touch-icon" sizes="120x120" href="{{ Storage::url('icons/apple-icon-120x120.png') }}">
<link rel="apple-touch-icon" sizes="144x144" href="{{ Storage::url('icons/apple-icon-144x144.png') }}">
<link rel="apple-touch-icon" sizes="152x152" href="{{ Storage::url('icons/apple-icon-152x152.png') }}">
<link rel="apple-touch-icon" sizes="180x180" href="{{ Storage::url('icons/apple-icon-180x180.png') }}">
<link rel="manifest" href="/manifest.json">
<meta name="theme-color" content="#ffffff">
<meta name="msapplication-config" content="{{ Storage::url('icons/browserconfig.xml')}}">
<meta name="msapplication-TileColor" content="#ffffff">
<meta name="msapplication-TileImage" content="{{ Storage::url('icons/ms-icon-144x144.png') }}">

<!-- scripts -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/roundSlider/1.3/roundslider.js"></script>
<script src="https://code.jquery.com/ui/1.13.2/jquery-ui.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id=G-0PDGC7ZSH0"></script>
<script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());

    // add the analytics id to the gtag
    gtag('config', '{{ config('services.google.analytics_id') }}', {
        'user_id': '{{ Auth::user()->analytics_id }}'
    });
</script>

@yield('additional_head') 