<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <style>
            html {
                overflow-y: scroll;
            }
            .manual-margins {
                margin-top: 4rem;
                margin-bottom: 6rem;
            }
            .manual-margin-top {
                margin-top: 3rem;
            }

            .note-content {
                word-wrap: break-word;
            }

            /* nav icons */
            .nav-icon-text i {
                font-size: 28px;
            }
            .nav-icon-text {
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                font-size: 14px;
            }
            .nav-link.active {
                color: #007bff;
            }
            .bi-star-fill {
                color: #ffd700;
            }
        </style>
    </head>

    <body>
        <nav class="navbar fixed-top navbar-expand-lg navbar-light bg-light">
            <div class="container-fluid">
                <ul class="navbar-nav">
                    @if(isset($showBackBtn) && $showBackBtn)
                        <li class="nav-item mr-auto">
                            <a class="nav-link" href="{{ route('button.back') }}">< Back {{ isset($activity) ? 'to '.$activity : ''}}</a>
                        </li>
                    @endif
                </ul>
                <ul class="navbar-nav">
                    <!-- if not set or not true, show it -->
                    @if (!(isset($hideProfileLink) && $hideProfileLink))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('profile') }}">Hi, {{ Auth::user()->name }}</a>
                        </li>
                    @else
                        <!-- otherwise show a logout button - unless on admin pages -->
                        <li class="nav-item ml-auto" @if(Str::startsWith(Request::route()->getName(), 'admin.')) hidden @endif>
                            <a class="nav-link" href="{{ route('logout') }}">Logout</a>
                        </li>
                    @endif
                </ul>
            </div>
        </nav>

        <div class="container manual-margins">
            <div class="row justify-content-center">
                @yield('content')
            </div>
        </div>

        @if (!(isset($hideBottomNav) && $hideBottomNav))
            <nav class="navbar fixed-bottom navbar-expand-lg navbar-light bg-light">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <!-- check the routename to set which is active -->
                        <a class="nav-link {{ Str::startsWith(Request::route()->getName(), 'explore.') ? 'active' : ''}}" href="{{ route('explore.browse') }}">
                            <span class="nav-icon-text"><i class="bi bi-ui-checks-grid"></i>Browse</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::route()->getName() == 'journal' ? 'active' : '' }}" href="{{ route('journal') }}">
                            <span class="nav-icon-text"><i class="bi bi-journal-plus"></i>Journal</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::route()->getName() == 'meditationLib' ? 'active' : '' }}" href="{{ route('meditationLib') }}">
                            <span class="nav-icon-text"><i class="bi bi-collection"></i>Library</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ Request::route()->getName() == 'favorites' ? 'active' : '' }}" href="{{ route('favorites') }}">
                            <span class="nav-icon-text"><i class="bi bi-star"></i>Favorites</span>
                        </a>
                    </li>
                </ul>
            </nav>
        @endif
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    </body>
</html>
