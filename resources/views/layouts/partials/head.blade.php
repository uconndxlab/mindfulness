@php
    use Illuminate\Support\Facades\URL;
    use Illuminate\Support\Str;
    use Illuminate\Support\Facades\Request;
@endphp

<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>@yield('title')</title>

<!-- css/js via Vite -->
@php $nonce = request()->attributes->get('csp_nonce'); @endphp
@vite(['resources/css/app.css', 'resources/js/app.js'])
@if($nonce)
    @livewireStyles(['nonce' => $nonce])
@else
    @livewireStyles
@endif

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

<meta name="csrf-token" content="{{ csrf_token() }}">

@yield('additional_head') 