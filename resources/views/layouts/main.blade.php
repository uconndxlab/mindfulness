<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="{{ URL::asset('main.css') }}" rel="stylesheet">
        <style>
            html {
                overflow-y: scroll;
            }
            .manual-margins {
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
            .tr-icon-text {
                font-size: 24px;
                margin-left: 10px;
            }
            .nav-link.active {
                color: #007bff;
            }
            .bi-star-fill {
                color: #ffd700;
            }
        </style>
    </head>
        @php
            $route_name = Request::route()->getName();
            $active_items = [false, false, false, false];
            if (Str::startsWith($route_name, 'explore.')) {
                if (Session::get('current_nav') == 'explore.home') {
                    $active_items[0] = true;
                }
                else {
                    $active_items[2] = true;
                }
            }
            else if ($route_name == 'journal') {
                $active_items[1] = true;
            }
            else if (Str::startsWith($route_name, 'library.')) {
                $active_items[2] = true;
            }
            else {
                $active_items[3] = true;
            }
        @endphp
    <body>
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid container">
                <ul class="navbar-nav">
                    @if(isset($back_route) && $back_route)
                        <li class="nav-item mr-auto">
                            <a class="nav-link" href="{{ $back_route }}">< Back {{ isset($back_label) ? $back_label : ''}}</a>
                        </li>
                    @endif
                </ul>

                <ul class="navbar-nav">
                    <!-- if not set or not true, show it -->
                    @if (!(isset($hide_account_link) && $hide_account_link))
                        <li class="nav-item">
                            <a class="nav-link" href="{{ route('account') }}">Hi, {{ Auth::user()->name }}
                                <i class="tr-icon-text bi bi-person-circle"></i>
                            </a>
                        </li>
                    @else
                        <!-- otherwise show a logout button - unless on admin pages -->
                        <li class="nav-item ml-auto" @if(Str::startsWith($route_name, 'admin.')) hidden @endif>
                            <a class="nav-link" href="{{ route('logout') }}">Logout
                                <i class="tr-icon-text bi bi-box-arrow-right"></i>
                            </a>
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
            <nav class="navbar fixed-bottom navbar-expand-lg navbar-light lower-nav-full">
                <div class="container">
                    <ul class="navbar-nav lower-nav mx-auto">
                        <li class="nav-item">
                            <!-- check the routename to set which is active -->
                            <a class="nav-link {{ $active_items[0] ? 'active' : '' }}" href="{{ route('explore.browse') }}">
                                <span class="nav-icon-text"><i class="bi bi-ui-checks-grid"></i>Browse</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $active_items[1] ? 'active' : '' }}" href="{{ route('journal') }}">
                                <span class="nav-icon-text"><i class="bi bi-journal-plus"></i>Journal</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $active_items[2] ? 'active' : '' }}" href="{{ route('library') }}">
                                <span class="nav-icon-text"><i class="bi bi-collection"></i>Library</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $active_items[3] ? 'active' : '' }}" href="{{ route('account') }}">
                                <span class="nav-icon-text"><i class="bi bi-person-circle"></i>Account</span>
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>
        @endif
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
    </body>
</html>
