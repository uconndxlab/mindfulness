@extends('layouts.main')

@section('title', 'Account')

@section('content')
<div class="col-md-8">
    <nav id="navbar-help" class="navbar navbar-expand-lg navbar-light sticky-top" id="navbar-help" style="background-color:white">
        <div class="tabs">
            <ul class="navbar-nav" style="flex-direction:row">
                <li class="nav-item" style="padding:0px 20px">
                    <a id="tutorial-link" class="nav-link" href="#Progress">Progress</a>
                </li>
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link" href="#Account">Account</a>
                </li>
            </ul>
        </div>
    </nav>

    <div data-bs-spy="scroll" data-bs-target="#navbar-help" data-bs-smooth-scroll="true" class="scrollspy-example pt-3 pb-3 rounded-2" tabindex="0"></div>
        <section id="Progress">

            <div class="text-center fs-5 fw-bold mt-5 mb-3">
                Progress
            </div>
            @foreach ($modules as $module)
            <div class="prior-note">
                <div class="grey-note">
                    <h5 class="fw-bold d-flex justify-content-between">
                        <span>{{ $module->name }}</span>
                    </h5>
                    <small class="{{ $module->progress['status'] == 'completed' ? 'fw-bold' : '' }}">{{ $module->progress['completed'] }}/{{ $module->progress['total'] }} days completed</small>
                </div>
            </div>
            @endforeach
        </section>

        <section id="Account">
            @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
            @endif
            <form method="POST" action="{{ route('user.update.namePass') }}">
                @csrf
                @Method('put')
            
                <div class="text-center fs-5 fw-bold mt-5 mb-3">
                    My Account
                </div>
                
                <div class="form-group mb-3">
                    <label class="fw-bold" for="name">Name</label>
                    <input id="name" type="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name', Auth::user()->name) }}">
                    @error('name')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                        </span>
                        @enderror
                </div>
                
                <div class="form-group mb-3">
                    <label class="fw-bold" for="password">New Password</label>
                    <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                    <small>(Must be at least 8 characters, including both letters and numbers.)</small>
                    @error('password')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
            
                <div class="form-group mb-3">
                    <label class="fw-bold" for="oldPass">Enter old password to confirm changes:</label>
                    <input id="oldPass" type="password" class="form-control @error('oldPass') is-invalid @enderror" name="oldPass">
                    @error('oldPass')
                    <span class="invalid-feedback" role="alert">
                        <strong>{{ $message }}</strong>
                    </span>
                    @enderror
                </div>
                <div class="text-center">
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">SAVE</button>
                    </div>
                </div>
            </form>
            
            @if (auth::user()->isAdmin())
                <div class="text-center mt-3">
                    <div class="form-group">
                        <a href="{{ route('admin.landing') }}" class="btn btn-danger">ADMIN: Content Management</a>
                    </div>
                </div>
            @endif
        </section>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        //scrollspy
        var scrollSpyContent = document.querySelector('.scrollspy-example');
        var navbar = document.getElementById('navbar-help');
        var navLinks = Array.from(navbar.querySelectorAll('.nav-link'));
        var sections = Array.from(document.querySelectorAll('section'));

        function getOffset(fromNavLinks = false) {
            //larger mobile offset - reaches contact, also consider if from navLinks to make sure top hits section title
            return window.innerWidth <= 768 && !fromNavLinks ? 150 : 70;
        }

        function updateActiveLink() {
            let fromTop = window.scrollY + getOffset();
            //get the current section
            let currentSection = sections.find(section => {
                let sectionTop = section.offsetTop;
                let sectionHeight = section.offsetHeight;
                return fromTop >= sectionTop && fromTop < sectionTop + sectionHeight;
            });

            if (currentSection) {
                //set the active link
                let newActiveLink = navbar.querySelector(`a[href="#${currentSection.id}"]`);
                if (newActiveLink && !newActiveLink.classList.contains('active')) {
                    navLinks.forEach(link => link.classList.remove('active'));
                    newActiveLink.classList.add('active');
                }
            }
        }

        //init active on launch
        updateActiveLink();

        //update on scroll
        window.addEventListener('scroll', updateActiveLink);

        //scrollspy smooth scroll to sections
        navLinks.forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let targetId = this.getAttribute('href');
                let targetSection = document.querySelector(targetId);
                //if going to contact section, use larger offset on mobile so that contact becomes active 
                let offset = targetId == 'contactUs' ? getOffset(false) : getOffset(true);
                let targetPosition = targetSection.offsetTop - offset + 1;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            });
        });
    });
</script>
@endsection
