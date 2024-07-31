@extends('layouts.main')

@section('title', 'Account')

@section('content')
<div class="col-md-8">
    <nav id="navbar-help" class="navbar navbar-expand-lg navbar-light sticky-top" id="navbar-help" style="background-color:white">
        <div class="tabs">
            <ul class="navbar-nav" style="flex-direction:row">
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link" href="#tutorial">Tutorial</a>
                </li>
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link" href="#FAQ">FAQ</a>
                </li>
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link" href="#contactUs">Contact Us</a>
                </li>
            </ul>
        </div>
    </nav>
    <div data-bs-spy="scroll" data-bs-target="#navbar-help" data-bs-root-margin="0px 0px -40%" data-bs-smooth-scroll="true" class="scrollspy-example p-3 rounded-2" tabindex="0">
        <div id="tutorial">
            <h5 class="text-center fw-bold mt-4">Tutorial:</h5>
            <x-contentView type="video" file="videoExampleSnarky.MOV"/>
        </div>
        
        <div id="FAQ">
            <h5 class="text-center fw-bold mt-4">FAQ</h5>
            <div class="col md-5">
                <div id="faqCarousel" class="carousel carousel-dark slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach ($faqs as $index => $faq)
                            <div class="carousel-item {{ $index == 0 ? 'active' : '' }}" data-bs-interval="60000">
                                <div class="d-flex justify-content-center align-items-center" style="height: 200px;">
                                    <div class="prior-note">
                                        <div class="top-note">
                                            <h5 class="fw-bold d-flex justify-content-between">
                                                <span>{{ $faq->question }}</span>
                                            </h5>
                                        </div>
                                        <p class="note-content">{{ $faq->answer }}</p>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#faqCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Previous</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#faqCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Next</span>
                    </button>
                </div>
            </div>
            <div class="accordion accordion-flush mb-3" id="filter_accordion">
                @foreach ($faqs as $index => $faq)
                    <div class="form-group accordion-item border mb-2">
                        <h2 class="accordion-header" id="headingFAQ_{{ $index }}">
                            <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFAQ_{{ $index }}" aria-expanded="true" aria-controls="collapseFAQ_{{ $index }}">
                                {{ $faq->question }}
                            </button>
                        </h2>
                        <div id="collapseFAQ_{{ $index }}" class="accordion-collapse collapse" aria-labelledby="headingFAQ_{{ $index }}">
                            <div class="accordion-body">
                                {{ $faq->answer }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>


        <div id="contactUs">
            <h5 class="text-center fw-bold mt-4">Contact us here:</h5>
            <p class="text-center">You can reach us via email or phone:</p>
            <p class="text-center">
                <a href="mailto:example@example.com" class="text-decoration-none">example@example.com</a>
            </p>
            <p class="text-center">
                <a href="tel:+1234567890" class="text-decoration-none">+1 (234) 567-890</a>
            </p>
        
            <h5 class="text-center fw-bold">Or use the Contact Form:</h5>
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            <form id="contact-form" action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Examples: bug, library help,...">
                    @error('subject')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="4">{{ old('message') }}</textarea>
                    @error('message')
                        <div class="text-danger">{{ $message }}</div>
                    @enderror
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        //url hash
        const hash = window.location.hash;
        if (hash) {
            //get corresponding hash
            const navLink = document.querySelector(`.nav-link[href="${hash}"]`);
            const section = document.querySelector(hash);

            //remove active from all
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            
            //add active to actual hash
            if (navLink) {
                navLink.classList.add('active');
            }
            
            //scroll
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
        }
        
        //prevent submission of empty form
        const contactForm = document.getElementById('contact-form');
        contactForm.addEventListener('submit', function (event) {
            event.preventDefault();
            var subjInput = document.getElementById('subject');
            var messageInput = document.getElementById('message');
            //if empty
            if (subjInput.value == ''  && messageInput.value.trim() == '') {
                //do not submit
                return;
            }
            this.submit();
        });

        //init scrollspy
        var scrollSpy = new bootstrap.ScrollSpy(document.querySelector('.scrollspy-example'), {
            target: '#navbar-help'
        });
    });
</script>
@endsection
