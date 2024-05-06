<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    </head>

    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <ul class="navbar-nav mr-auto">
                    <!-- show back if backRoute is set -->
                    @if(isset($backRoute))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $backRoute }}">< Back</a>
                        </li>
                    @endif
                </ul>
                <ul class="navbar-nav">
                    <!-- show if not set OR true -->
                    @if(!isset($showProfileLink) || $showProfileLink)
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile') }}">Hi, {{ Auth::user()->name }}</a>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>

        <div class="container">
            @yield('content')
        </div>

        <nav class="navbar fixed-bottom navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <!-- logic to set active - checks the route -->
                        <a class="nav-link {{ Str::startsWith(Request::route()->getName(), 'explore.') ? 'active' : ''}}" href="{{ route('explore.resume') }}">
                            <span>Browse</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::route()->getName() == 'journal' ? 'active' : '' }}" href="{{ route('journal') }}">
                            <span>Journal</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>
    </body>
</html>
