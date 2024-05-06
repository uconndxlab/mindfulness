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
                        @if(isset($backRoute))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ $backRoute }}">< Back</a>
                        </li>
                        @endif
                    </ul>
                    <ul class="navbar-nav">
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile') }}">Hi, {{ Auth::user()->name }}</a>
                        </li>
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
                        <a class="nav-link" href="{{ route('explore.home') }}">
                            <i class="bi bi-house-door"></i>
                            <span>Browse</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('journal') }}">
                            <i class="bi bi-person"></i>
                            <span>Journal</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>


        <nav class="navbar navbar-expand-lg navbar-light bg-light">
    <div class="container-fluid">
            
        </div>
    </div>
</nav>




    </body>
</html>

<!-- navbar-expand-lg navbar-light bg-light  -->
