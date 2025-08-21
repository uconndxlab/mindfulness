@extends('layouts.app')

@section('title', 'Help')
@section('page_id', 'help')

@section('content')
<div class="col-md-8">
    <nav id="navbar-help" class="navbar navbar-expand navbar-light sticky-top top-nav" id="navbar-help">
        <div class="tabs">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a id="tutorial-link" class="nav-link" href="#tutorial">Tutorial</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#info">Info</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#FAQ">FAQ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#contactUs">Contact</a>
                </li>
            </ul>
        </div>
    </nav>
    <div data-bs-spy="scroll" data-bs-target="#navbar-help" data-bs-smooth-scroll="true" class="scrollspy-example pt-3 pb-3 rounded-2" tabindex="0">
        <section id="tutorial">
            <h4 class="text-center fw-bold mt-4">Tutorial</h4>
            <div class="container tutorial-container">
                <x-contentView id="welcome_video" type="video" file="Healing Hearts App Tutorial 12 5.mp4" controlsList="noplaybackrate nodownload noseek"/>
            </div>
        </section>
        <section id="info">
            <h4 class="text-center fw-bold mt-4">Info about Healing Hearts</h4>
            <h5 class="text-center fw-bold mt-2">Acknolwedgement</h5>
            <p class="text-center">
                The development and testing of the Healing Hearts program was made possible through the generous support of the National Institute of Health (K01MH122502). We gratefully acknowledge their funding. We also extend our heartfelt thanks to our dedicated team members from UConn Digital Experience Group, research assistants, mindfulness teachers, consultants, and collaborators for their invaluable contributions to this project. We are also grateful to the participants who provided valuable feedback during the development and testing phase, helping us to continuously improve the Healing Hearts experience. 
            </p>
            <h5 class="text-center fw-bold mt-4">Our Teachers</h5>
            <div class="container">
                @foreach ($teachers as $teacher)
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="row g-0 flex-column flex-md-row">
                                    <div class="col-12 col-md-4 col-lg-3">
                                        <div class="teacher-image-container">
                                            <img src="{{ Storage::url('profile_pictures/'.$teacher->profile_picture) }}" 
                                                alt="{{ $teacher->name }}" 
                                                class="teacher-image">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-8 col-lg-9">
                                        <div class="card-body">
                                            <h5 class="card-title" id="teacher-name-{{ $loop->index }}">{{ $teacher->name }}</h5>
                                            <p class="card-text d-none d-md-block">{{ $teacher->bio }}</p>
                                            <div class="card-text d-md-none">
                                                <div class="short-bio">{{ Str::limit($teacher->bio, 150) }}</div>
                                                <div class="full-bio d-none">{{ $teacher->bio }}</div>
                                            </div>
                                            <button class="btn btn-link read-more p-0 d-md-none" data-teacher-index="{{ $loop->index }}">Read More</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>
        
        <section id="FAQ">
            <h4 class="text-center fw-bold mt-4">FAQ</h4>
            <div class="accordion accordion-flush mb-3" id="filter_accordion">
                @foreach ($faqs as $index => $faq)
                    <div class="form-group accordion-item border mb-2">
                        <h2 class="accordion-header" id="headingFAQ_{{ $index }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFAQ_{{ $index }}" aria-expanded="true" aria-controls="collapseFAQ_{{ $index }}">
                                {{ $faq->question }}
                            </button>
                        </h2>
                        <div id="collapseFAQ_{{ $index }}" class="accordion-collapse collapse" aria-labelledby="headingFAQ_{{ $index }}">
                            <div class="accordion-body faq-body">
                                {!! $faq->answer !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="contactUs">
            <h4 class="text-center fw-bold mt-4">Contact</h4>
            <p class="text-center fw-bold">Get Immediate Help in a Crisis:</p>
            <p class="text-center">Call 911 if you or someone you know is in immediate danger or go to the nearest emergency room.</p>
            <p class="text-center fw-bold mt-3">For matters related to this app:</p>
            <p class="text-center">You can reach us via email or phone:</p>
            <p class="text-center">
                <a href="mailto:{{ config('mail.contact_email') }}" class="text-decoration-none">{{ config('mail.contact_email') }}</a>
            </p>
            <p class="text-center">
                <a href="tel:{{ config('mail.contact_phone') }}" class="text-decoration-none">{{ formatPhone(config('mail.contact_phone')) }}</a>
            </p>
        
            <p class="text-center fw-bold">Or use the Contact Form:</p>
            @livewire('contact-form')
        </section>
    </div>
</div>
@endsection
