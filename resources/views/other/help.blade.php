@extends('layouts.main')

@section('title', 'Account')

@section('content')
<div class="col-md-8">
    
    <h2 class="text-left fw-bold mb-5">Help</h>
    
    <h5 class="text-center fw-bold mt-4">Tutorial:</h5>
    <x-contentView type="video" file="videoExampleSnarky.MOV"/>
    
    <h5 class="text-center fw-bold mt-4">FAQ</h5>
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
    <form action="{{ route('contact.submit') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label for="subject" class="form-label">Subject</label>
            <input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject') }}">
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
@endsection
