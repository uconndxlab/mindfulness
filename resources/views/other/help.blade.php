@extends('layouts.main')

@section('title', 'Help')

@section('content')
<div class="col-md-8">
    <nav id="navbar-help" class="navbar navbar-expand-lg navbar-light sticky-top" id="navbar-help" style="background-color:white">
        <div class="tabs">
            <ul class="navbar-nav" style="flex-direction:row">
                <li class="nav-item" style="padding:0px 20px">
                    <a id="tutorial-link" class="nav-link" href="#tutorial">Tutorial</a>
                </li>
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link" href="#info">Info</a>
                </li>
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link" href="#FAQ">FAQ</a>
                </li>
                <li class="nav-item" style="padding:0px 20px">
                    <a class="nav-link" href="#contactUs">Contact</a>
                </li>
            </ul>
        </div>
    </nav>
    <div data-bs-spy="scroll" data-bs-target="#navbar-help" data-bs-smooth-scroll="true" class="scrollspy-example pt-3 pb-3 rounded-2" tabindex="0">
        <section id="tutorial">
            <h4 class="text-center fw-bold mt-4">Tutorial:</h4>
            <x-contentView type="video" file="" controlsList="noplaybackrate nodownload noseek"/>
        </section>
        <section id="info">
            <h4 class="text-center fw-bold mt-4">Info about Healing Hearts:</h4>
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
                                                <div class="full-bio" style="display: none;">{{ $teacher->bio }}</div>
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
                            <div style="padding: 10px 20px !important;" class="accordion-body">
                                {!! $faq->answer !!}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="contactUs">
            <h4 class="text-center fw-bold mt-4">Contact:</h4>
            <p class="text-center fw-bold">Get Immediate Help in a Crisis</p>
            <p class="text-center">Call 911 if you or someone you know is in immediate danger or go to the nearest emergency room.</p>
            <p class="text-center fw-bold mt-3">For matters related to this app:</p>
            <p class="text-center">You can reach us via email or phone:</p>
            <p class="text-center">
                <a href="mailto:{{ config('mail.contact_email') }}" class="text-decoration-none">{{ config('mail.contact_email') }}</a>
            </p>
            <p class="text-center">
                <a href="tel:{{ config('mail.contact_phone') }}" class="text-decoration-none">{{ formatPhone(config('mail.contact_phone')) }}</a>
            </p>
        
            <h5 class="text-center fw-bold">Or use the Contact Form:</h5>
            <div id="success-messages" class="alert alert-success contact-message" style="display: none;"></div>
            <div id="error-messages" class="alert alert-danger contact-message" style="display: none;"></div>
            <form id="contact-form" action="{{ route('contact.submit') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="subject" class="form-label">Subject</label>
                    <input type="text" class="form-control" id="subject" name="subject" value="{{ old('subject') }}" placeholder="Examples: bug, library help,...">
                    <div id="error-messages-subject" class="text-danger contact-message" style="display: none;"></div>
                </div>
                <div class="mb-3">
                    <label for="message" class="form-label">Message</label>
                    <textarea class="form-control" id="message" name="message" rows="4">{{ old('message') }}</textarea>
                    <div id="error-messages-message" class="text-danger contact-message" style="display: none;"></div>
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </section>
    </div>
</div>
<style>
    .teacher-image-container {
        position: relative;
        padding-top: 75%;
        overflow: hidden;
    }

    .teacher-image {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (max-width: 767.98px) {
        .teacher-image-container {
            max-width: 200px;
            margin: 1rem auto;
        }
    }

    @media (min-width: 768px) {
        .teacher-image-container {
            height: 100%;
            padding-top: 0;
        }

        .teacher-image {
            position: static;
            height: 100%;
            object-fit: cover;
        }

        .card .row {
            height: 100%;
        }
    }
</style>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        //prevent submission of empty form
        const errDiv = document.getElementById('error-messages');
        const subjErrDiv = document.getElementById('error-messages-subject');
        const msgErrDiv = document.getElementById('error-messages-message');
        const successDiv = document.getElementById('success-messages');

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

            closeResponseMessages();
            return new Promise((resolve, reject) => {
                axios.post('{{ route('contact.submit') }}', {
                    subject: subjInput.value,
                    message: messageInput.value.trim()
                }, {
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(response => {
                    if (response.data?.success) {
                        console.log(response.data.success);
                        successDiv.textContent = response.data.success;
                        successDiv.style.display = 'block';
                    }
                    resolve(true);
                })
                .catch(error => {
                    console.error('Error submitting form: ', error);
                    //display error
                    if (error.response?.data?.errors) {
                        if (error.response.data.errors.subject) {
                            subjErrDiv.textContent = error.response.data.errors.subject.join(', ');
                            subjErrDiv.style.display = 'block';
                        }
                        if (error.response.data.errors.message) {
                            msgErrDiv.textContent = error.response.data.errors.message.join(', ');
                            msgErrDiv.style.display = 'block';
                        }
                    } else {
                        //other errors
                        const errorMessages = error.response?.data?.error_message || 'An unknown error occurred.';
                        errDiv.textContent = errorMessages;
                        errDiv.style.display = 'block';
                    }
                    reject(false);
                });
            });
        });

        function closeResponseMessages() {
            document.querySelectorAll('.contact-message').forEach(msg => {
                msg.textContent = '';
                msg.style.display = 'none';
            });
        }

        //scrollspy
        var scrollSpyContent = document.querySelector('.scrollspy-example');
        var navbar = document.getElementById('navbar-help');
        var navLinks = Array.from(navbar.querySelectorAll('.nav-link'));
        var sections = Array.from(document.querySelectorAll('section'));

        function getOffset(fromNavLinks = false) {
            //larger mobile offset - reaches contact, also consider if from navLinks to make sure top hits section title
            return window.innerWidth <= 768 && !fromNavLinks ? 200 : 200;
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
                let targetPosition = targetSection.offsetTop - offset + 125;

                window.scrollTo({
                    top: targetPosition,
                    behavior: 'smooth'
                });
            });
        });

        //teachers read more
        document.querySelectorAll('.read-more').forEach(button => {
        button.addEventListener('click', function() {
            const card = this.closest('.card');
            const cardBody = this.closest('.card-body');
            const shortBio = cardBody.querySelector('.short-bio');
            const fullBio = cardBody.querySelector('.full-bio');
            const teacherIndex = this.getAttribute('data-teacher-index');
            
            if (shortBio.style.display !== 'none') {
                shortBio.style.display = 'none';
                fullBio.style.display = 'block';
                this.textContent = 'Read Less';

                //smooth scroll to bio
                const teacherName = card.querySelector(`#teacher-name-${teacherIndex}`);
                if (teacherName) {
                    const offset = 60;
                    const elementPosition = teacherName.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            } else {
                shortBio.style.display = 'block';
                fullBio.style.display = 'none';
                this.textContent = 'Read More';
                
                //smooth scroll back to teacher
                const teacherElement = card.querySelector(`.teacher-image-container`);
                if (teacherElement) {
                    const offset = 70;
                    const elementPosition = teacherElement.getBoundingClientRect().top;
                    const offsetPosition = elementPosition + window.pageYOffset - offset;
                    window.scrollTo({
                        top: offsetPosition,
                        behavior: 'smooth'
                    });
                }
            }
        });
    });
    });
</script>
@endsection
