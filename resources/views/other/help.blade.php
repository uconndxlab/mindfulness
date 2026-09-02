@extends('layouts.app')

@section('title', 'About')
@section('page_id', 'help')

@section('content')
<div class="col-12 about-page px-0">
    <div class="container about-page-body pb-4">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <section id="info" class="about-card">
                    <h2 class="about-heading">Info about {{ config('app.name') }}</h2>
                    <h3 class="about-subheading">Acknowledgement</h3>
                    <p>
                        The development and testing of the {{ config('app.name') }} program was made possible through the generous support of the National Institute of Health (K01MH122502). We gratefully acknowledge their funding. We also extend our heartfelt thanks to our dedicated team members from UConn Digital Experience Group, research assistants, mindfulness teachers, consultants, and collaborators for their invaluable contributions to this project. We are also grateful to the participants who provided valuable feedback during the development and testing phase, helping us to continuously improve the {{ config('app.name') }} experience.
                    </p>
                    <h3 class="about-subheading">{{ config('app.name') }} Team</h3>
                    @foreach ($teachers as $teacher)
                        <div class="teacher-row row g-0 flex-column flex-md-row {{ !$loop->last ? 'mb-4 pb-4 border-bottom' : '' }}">
                            <div class="col-12 col-md-4 col-lg-3">
                                <div class="teacher-image-container">
                                    <img src="{{ Storage::url('profile_pictures/34/'.$teacher->profile_picture) }}"
                                        alt="{{ $teacher->name }}"
                                        class="teacher-image">
                                </div>
                            </div>
                            <div class="col-12 col-md-8 col-lg-9">
                                <div class="card-body px-0 px-md-3 py-2">
                                    <h4 class="card-title" id="teacher-name-{{ $loop->index }}">{{ $teacher->name }}</h4>
                                    <div class="mb-1">
                                        @markdown($teacher->title)
                                    </div>
                                    <p class="card-text d-none d-md-block">{{ $teacher->bio }}</p>
                                    <div class="card-text d-md-none">
                                        <div class="short-bio">{{ Str::limit($teacher->bio, 150) }}</div>
                                        <div class="full-bio d-none">{{ $teacher->bio }}</div>
                                    </div>
                                    <button type="button" class="btn btn-link read-more p-0 d-md-none" data-teacher-index="{{ $loop->index }}">Read More</button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </section>

                <section id="resources" class="about-card">
                    <h2 class="about-heading">Resources</h2>
                    <p class="about-card-lead">A few things to help you get started. You can come back here any time.</p>

                    <div class="about-resource" id="welcome-letter">
                        <h3 class="about-resource-title">Welcome Letter</h3>
                        <p class="about-resource-desc">Tap the letter and scroll it as you read.</p>
                        <div class="welcome-letter-paper welcome-letter-paper--preview about-letter-preview" data-open-letter-modal role="button" tabindex="0" aria-label="Open welcome letter">
                            @include('components.welcome-letter')
                        </div>
                    </div>

                    <div class="about-resource" id="researcher">
                        <h3 class="about-resource-title">A Brief Welcome from the Researcher</h3>
                        <p class="about-resource-desc">Meet the researcher in a video.</p>
                        <div class="about-resource-media">
                            <x-contentView
                                id="about_pi_video"
                                type="video"
                                path="{{ config('welcome.pi_video') }}"
                                :allowSeek="true"
                                controlsList="nodownload noplaybackrate"
                            />
                        </div>
                    </div>

                    <div class="about-resource">
                        <h3 class="about-resource-title">App Tutorial</h3>
                        <p class="about-resource-desc">Watch this video to take a tour of the app.</p>
                        <div class="about-resource-actions">
                            <a class="btn btn-primary" href="#tutorial">Watch</a>
                        </div>
                    </div>

                    <div class="about-resource">
                        <h3 class="about-resource-title" id="workbook">Workbook</h3>
                        <p class="about-resource-desc">Viewable in each Part.</p>
                        <div class="about-resource-actions">
                            <x-pdf-viewer fpath="{{ Storage::url(config('welcome.workbook_pdf')) }}" wbName="{{ config('app.name') }} Workbook" />
                        </div>
                    </div>

                    <div class="about-resource">
                        <h3 class="about-resource-title">Parent Testimonials</h3>
                        <p class="about-resource-desc">Hear from other parents who have used {{ config('app.name') }}.</p>
                        <div class="about-resource-actions">
                            <a class="btn btn-primary" href="{{ url('/study') }}">View</a>
                            <a class="btn btn-primary" href="{{ config('welcome.testimonial_url') }}">Submit a testimonial</a>
                        </div>
                    </div>
                </section>

                <section id="tutorial" class="about-card">
                    <h2 class="about-heading">Tutorial</h2>
                    <p class="about-card-lead">Watch this video to take a tour of the app.</p>
                    <div class="about-resource-media">
                        <x-contentView
                            id="tutorial_video"
                            type="video"
                            path="{{ config('tutorial.video_file') }}"
                            :allowSeek="true"
                            controlsList="nodownload noplaybackrate"
                        />
                    </div>
                </section>

                <section id="FAQ" class="about-card">
                    <h2 class="about-heading">Frequently Asked Questions</h2>
                    <div class="accordion accordion-flush mb-0" id="faq_categories">
                        @foreach ($categories as $category)
                            @php
                                $categoryFaqs = $faqs->get($category->value);
                                $faqCount = $categoryFaqs ? $categoryFaqs->count() : 0;
                            @endphp

                            @if($faqCount > 0)
                                <div class="accordion-item border mb-2">
                                    <h2 class="accordion-header" id="heading_{{ $category->value }}">
                                        <button class="accordion-button collapsed"
                                            type="button"
                                            data-bs-toggle="collapse"
                                            data-bs-target="#collapse_{{ $category->value }}"
                                            aria-expanded="false"
                                            aria-controls="collapse_{{ $category->value }}">
                                            {{ $category->label() }}
                                        </button>
                                    </h2>

                                    <div id="collapse_{{ $category->value }}"
                                        class="accordion-collapse collapse m-3"
                                        aria-labelledby="heading_{{ $category->value }}"
                                        data-bs-parent="#faq_categories">
                                        <div class="accordion-body p-0">
                                            <div class="accordion accordion-flush" id="faq_{{ $category->value }}">
                                                @foreach ($categoryFaqs as $faq)
                                                    <div class="form-group accordion-item border {{ !$loop->last ? 'mb-2' : '' }}">
                                                        <h3 class="accordion-header" id="heading_faq_{{ $faq->id }}">
                                                            <button class="accordion-button collapsed"
                                                                type="button"
                                                                data-bs-toggle="collapse"
                                                                data-bs-target="#collapse_faq_{{ $faq->id }}"
                                                                aria-expanded="false"
                                                                aria-controls="collapse_faq_{{ $faq->id }}">
                                                                {{ $faq->question }}
                                                            </button>
                                                        </h3>
                                                        <div id="collapse_faq_{{ $faq->id }}"
                                                            class="accordion-collapse collapse"
                                                            aria-labelledby="heading_faq_{{ $faq->id }}"
                                                            data-bs-parent="#faq_{{ $category->value }}">
                                                            <div class="accordion-body faq-body">
                                                                <div class="faq-answer">
                                                                    @markdown(is_string($faq->answer ?? null) ? $faq->answer : '')
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </section>

                <section id="contactUs" class="about-card">
                    <h2 class="about-heading">Contact</h2>
                    <p class="fw-bold mb-1">Get Immediate Help in a Crisis:</p>
                    <p>Call 911 if you or someone you know is in immediate danger or go to the nearest emergency room.</p>
                    <p class="fw-bold mt-3 mb-1">For matters related to this app:</p>
                    <p class="mb-1">You can reach us via email or phone:</p>
                    <p class="mb-1">
                        <a href="mailto:{{ config('mail.contact_email') }}" class="text-decoration-none">{{ config('mail.contact_email') }}</a>
                    </p>
                    <p>
                        <a href="tel:{{ config('mail.contact_phone') }}" class="text-decoration-none">{{ formatPhone(config('mail.contact_phone')) }}</a>
                    </p>
                    <p class="fw-bold">Or use the Contact Form:</p>
                    @livewire('contact-form')
                </section>
            </div>
        </div>
    </div>
</div>

@include('components.welcome-letter-modal')
@endsection
