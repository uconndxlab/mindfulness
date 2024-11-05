<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>@yield('title')</title>
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
        <link href="{{ URL::asset('main.css') }}" rel="stylesheet">
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/roundSlider/1.3/roundslider.js"></script>
        <style>
            html {
                overflow-y: scroll;
                scroll-behavior: smooth;
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
            /* .tr-icon-text {
                font-size: 24px;
                margin-left: 10px;
            } */
            .nav-link.active {
                color: #007bff;
            }
            .bi-star-fill {
                color: #ffd700;
            }
            .sticky-top {
                position: -webkit-sticky;
                position: sticky;
                top: 0;
                z-index: 1020;
            }
            .accordion-button.disabled {
                pointer-events: none;
                opacity: 0.5;
            }
        </style>
    </head>
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
    <body>
        <nav class="navbar navbar-expand-lg navbar-light">
            <div class="container-fluid container">
                <ul class="navbar-nav">
                    @if(isset($page_info['back_route']) && isset($page_info['back_label']))
                        <li class="nav-item mr-auto">
                            <a class="nav-link" href="{{ $page_info['back_route'] }}"><i class="bi bi-arrow-left"></i>{{ $page_info['back_label'] }}</a>
                        </li>
                    @endif
                </ul>

                @if (!(isset($page_info['hide_bottom_nav']) && $page_info['hide_bottom_nav']))
                    <ul class="navbar-nav">
                        @if (!(isset($page_info['hide_account_link']) && $page_info['hide_account_link']))
                            <li class="nav-item">
                                <i><a class="nav-link" href="{{ route('account') }}">Hi, {{ Auth::user()->name }}</a></i>
                            </li>
                        @else
                            <li class="nav-item ml-auto">
                                <a class="nav-link" href="{{ route('logout') }}">Logout
                                    <i class="bi bi-box-arrow-right"></i>
                                </a>
                            </li>
                        @endif
                    </ul>
                @endif
            </div>
        </nav>

        <div class="container manual-margins">
            <div class="row justify-content-center">

                <div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title" id="appModalLabel"></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>
                            <div id="appModalBody" class="modal-body">
                            </div>
                            <img id="appModalImg" src="" alt="Example Image" class="img-fluid mb-3" style="display: none;">
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                                <a id="additionalBtn" class="btn btn-primary" style="display: none;"></a>
                            </div>
                        </div>
                    </div>
                </div>
                @yield('content')
            </div>
        </div>

        @if (!(isset($page_info['hide_bottom_nav']) && $page_info['hide_bottom_nav']))
            <nav class="navbar fixed-bottom navbar-expand-lg navbar-light lower-nav-full">
                <div class="container">
                    <ul class="navbar-nav lower-nav mx-auto">
                        <li class="nav-item">
                            <a class="nav-link {{ $active_items[0] ? 'active' : '' }}" href="{{ route('explore.browse', ['active' => $active_items[0]]) }}">
                                <span class="nav-icon-text"><i class="bi bi-ui-checks-grid"></i>Guides</span>
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
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-kenU1KFdBIe4zVF0s0G1M5b4hcpxyD9F7jL+jjXkk+Q2h455rYXK/7HAuoJl+0I4" crossorigin="anonymous"></script>
        <script>
            function showModal(label='undefined', body='', media=null, additionalRte=null, additionalRteLabel='Continue') {
                var myModal = new bootstrap.Modal(document.getElementById('appModal'));
                document.getElementById('appModalLabel').innerHTML = label;
                document.getElementById('appModalBody').innerHTML = body;
                if (media) {
                    var modalMedia = document.getElementById('appModalImg');
                    modalMedia.src = media;
                    modalMedia.style.display = 'block';
                }
                else {
                    document.getElementById('appModalImg').style.display = 'none';
                }

                const additionalBtn = document.getElementById('additionalBtn');
                if (additionalRte) {
                    additionalBtn.innerHTML = additionalRteLabel;
                    additionalBtn.style.display = 'inline-block';
                    additionalBtn.href = additionalRte;
                } else {
                    additionalBtn.style.display = 'none';
                }

                myModal.show();
            }
            @if(session('modal_data'))
                @php
                    $modalData = session('modal_data');
                    $label = $modalData['label'] ?? 'undefined';
                    $body = $modalData['body'] ?? '';
                    $media = $modalData['media'] ?? null;
                    $additionalRte = $modalData['additionalRte'] ?? null;
                    $additionalRteLabel = $modalData['additionalRteLabel'] ?? 'Continue';
                @endphp
                showModal(
                    {!! json_encode($label) !!}, 
                    {!! json_encode($body) !!}, 
                    {!! json_encode($media) !!}, 
                    {!! json_encode($additionalRte) !!}, 
                    {!! json_encode($additionalRteLabel) !!}
                );
                {{ session()->forget('modal_data') }}
            @endif
        </script>
    </body>
</html>
