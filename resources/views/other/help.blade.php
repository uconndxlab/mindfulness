@extends('layouts.main')

@section('title', 'Account')

@section('content')
<div class="col-md-8">
    
    <div class="text-left fs-2 fw-bold mt-5 mb-3">
        Help
    </div>

    
    <div class="text-center fs-5 fw-bold mt-5 mb-3">
        Tutorial video:
    </div>
    <x-contentView type="video" file="videoExampleSnarky.MOV"/>
    
    <div class="text-center fs-5 fw-bold mt-5 mb-3">
        FAQ
    </div>
    @foreach ($faqs as $faq)
    <div class="prior-note">
        <div class="top-note">
            <h5 class="fw-bold d-flex justify-content-between">
                <span>{{ $faq->question }}</span>
            </h5>
        </div>
        <p class="note-content">{{ $faq->answer }}</p>
    </div>
    @endforeach

    <div class="text-center fs-5 fw-bold mt-5 mb-3">
        Contact Us
    </div>
    <p class="text-center">You can reach us via email or phone:</p>
    <p class="text-center">
        <a href="mailto:example@example.com" class="text-decoration-none">example@example.com</a>
    </p>
    <p class="text-center">
        <a href="tel:+1234567890" class="text-decoration-none">+1 (234) 567-890</a>
    </p>
</div>
@endsection
