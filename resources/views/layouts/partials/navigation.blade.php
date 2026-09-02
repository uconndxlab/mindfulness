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

    $page_info = $page_info ?? [];
    $hideTopNav = !empty($page_info['hide_top_nav']) || $route_name === 'explore.home';
    $showLogout = $route_name === 'account';
    $navColor = $page_info['nav_color'] ?? null;
    $isJournalNav = Str::startsWith($route_name, 'journal.');
    $isLibraryNav = Str::startsWith($route_name, 'library.');
    $isHelpNav = $route_name === 'help';
    $hasBack = isset($page_info['back_route']) && isset($page_info['back_label']);
@endphp

@if (!$hideTopNav)
<nav @if ($isHelpNav) id="navbar-help" @endif class="navbar navbar-expand-lg sticky-top top-nav app-top-nav"@if ($navColor) style="--top-nav-accent: {{ $navColor }}"@endif>
    <div class="container-fluid container app-top-nav-inner">
        @if ($isHelpNav)
            <div class="about-tabs-wrap app-top-nav-tabs">
                <ul class="navbar-nav flex-row flex-nowrap">
                    <li class="nav-item">
                        <a class="nav-link" href="#info">Info</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#resources">Resources</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#tutorial">Tutorial</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#FAQ">FAQ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#contactUs">Contact</a>
                    </li>
                </ul>
            </div>
        @elseif ($isJournalNav)
            <div class="about-tabs-wrap app-top-nav-tabs">
                <ul class="navbar-nav flex-row flex-nowrap">
                    <li class="nav-item">
                        <a class="nav-link {{ $route_name == 'journal.compose' ? 'active disabled' : '' }}" href="{{ $route_name == 'journal.compose' ? '' : route('journal.compose') }}">Write</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $route_name == 'journal.library' ? 'active disabled' : '' }}" href="{{ $route_name == 'journal.library' ? '' : route('journal.library') }}">History</a>
                    </li>
                </ul>
            </div>
        @elseif ($isLibraryNav)
            <div class="about-tabs-wrap app-top-nav-tabs">
                <ul class="navbar-nav flex-row flex-nowrap">
                    <li class="nav-item">
                        <a class="nav-link {{ $route_name == 'library.favorites' ? 'active disabled' : '' }}" href="{{ $route_name == 'library.favorites' ? '' : route('library.favorites') }}">Favorites</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ $route_name == 'library.main' ? 'active disabled' : '' }}" href="{{ $route_name == 'library.main' ? '' : route('library.main') }}">Search</a>
                    </li>
                </ul>
            </div>
        @else
            <ul class="navbar-nav">
                @if ($hasBack)
                    <li class="nav-item mr-auto">
                        <a class="nav-link btn btn-nav app-nav-pill" href="{{ $page_info['back_route'] }}" id="backButton">
                            <i class="bi bi-arrow-left"></i>{{ $page_info['back_label'] }}
                        </a>
                    </li>
                @endif
            </ul>
        @endif

        @if ($showLogout)
            <ul class="navbar-nav">
                <li class="nav-item ml-auto">
                    <button id="logoutBtn" class="nav-link btn btn-nav app-nav-pill fw-semibold">Logout
                        <i class="bi bi-box-arrow-right"></i>
                    </button>
                </li>
            </ul>
        @endif
    </div>
</nav>
@endif

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
