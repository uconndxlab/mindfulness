<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
        <style>
            .manual-margins {
                margin-top: 4rem;
                margin-bottom: 4rem;
            }
        </style>
    </head>

    <body>
        <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
            <ul class="navbar-nav mr-auto">
                <!-- show back if backRoute is set -->
                @if(isset($backRoute))
                    <li class="nav-item">
                        <a class="nav-link" href="{{ $backRoute }}">< Back</a>
                    </li>
                @endif
            </ul>
            <ul class="navbar-nav">
                <!-- if not set, assume true -->
                @if(!isset($showProfileLink) || $showProfileLink)
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile') }}">Hi, {{ Auth::user()->name }}</a>
                    </li>
                @else
                <!-- otherwise show a logout button -->
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                    </li>
                @endif
            </ul>
        </nav>

        <div class="container manual-margins">
            <div class="row justify-content-center">
                @yield('content')
            </div>
        </div>

        <nav class="navbar fixed-bottom navbar-expand-lg navbar-light bg-light">
            <ul class="navbar-nav mx-auto">
                <li class="nav-item">
                    <!-- check the routename to set which is active -->
                    <a class="nav-link {{ Str::startsWith(Request::route()->getName(), 'explore.') ? 'active' : ''}}" href="{{ route('explore.browse') }}">
                        <span>Browse</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ Request::route()->getName() == 'journal' ? 'active' : '' }}" href="{{ route('journal') }}">
                        <span>Journal</span>
                    </a>
                </li>
            </ul>
        </nav>
    </body>
</html>
