@php
    $route_name = Request::route()->getName();
    $active_items = [false, false, false, false, false];
    if (!(isset($page_info['hide_bottom_nav']) && $page_info['hide_bottom_nav'])) {
        if (Str::startsWith($route_name, 'explore.')) {
            $active_items[0] = true;
        }
        else if (Str::startsWith($route_name, 'journal.')) {
            $active_items[1] = true;
        }
        else if (Str::startsWith($route_name, 'library.')) {
            $active_items[2] = true;
        }
        else if ($route_name == 'account') {
            $active_items[3] = true;
        }
        else if ($route_name == 'help') {
            $active_items[4] = true;
        }
    }
@endphp

<nav class="navbar navbar-expand-lg navbar-light">
    <div class="container-fluid container">
        <ul class="navbar-nav">
            @if(isset($page_info['back_route']) && isset($page_info['back_label']))
                <li class="nav-item mr-auto">
                    <a class="nav-link btn btn-nav" href="{{ $page_info['back_route'] }}" id="backButton">
                        <i class="bi bi-arrow-left"></i>{{ $page_info['back_label'] }}
                    </a>
                </li>
            @endif
        </ul>

        @if (!(isset($page_info['hide_bottom_nav']) && $page_info['hide_bottom_nav']))
            <ul class="navbar-nav">
                <li class="nav-item ml-auto">
                    <button id="logoutBtn" class="nav-link btn btn-nav fw-semibold">Logout
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </li>
            </ul>
        @endif
    </div>
</nav>

@if (!(isset($page_info['hide_bottom_nav']) && $page_info['hide_bottom_nav']))
    <nav class="navbar fixed-bottom navbar-expand-lg navbar-light lower-nav-full">
        <div class="container">
            <ul class="navbar-nav lower-nav mx-auto">
                <li class="nav-item">
                    <a class="nav-link {{ $active_items[0] ? 'active' : '' }}" href="{{ route('explore.browse', ['active' => $active_items[0]]) }}">
                        <span class="nav-icon-text"><i class="bi bi-ui-checks-grid"></i>Home</span>
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
                        <span class="nav-icon-text"><i class="bi bi-person-circle"></i>Profile</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $active_items[4] ? 'active' : '' }}" href="{{ route('help') }}">
                        <span class="nav-icon-text"><i class="bi bi-book"></i>About</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
@endif 