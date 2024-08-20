@extends('layouts.main')

@section('title', 'Help')

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
    <div data-bs-spy="scroll" data-bs-target="#navbar-help" data-bs-smooth-scroll="true" class="scrollspy-example pt-3 pb-3 rounded-2" tabindex="0">
        <section id="tutorial">
            <h5 class="text-center fw-bold mt-4">Tutorial:</h5>
            <x-contentView type="video" file="videoExampleSnarky.MOV"/>
        </section>
        
        <section id="FAQ">
            <h5 class="text-center fw-bold mt-4">FAQ</h5>
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
                                {{ $faq->answer }}
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </section>

        <section id="contactUs">
            <h5 class="text-center fw-bold mt-4">Contact us here:</h5>
            <p class="text-center">*** IN CASE OF IMMMEDIATE HELP OR CRISIS, DIAL 911 ***</p>
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

        //init scrollspy
        var scrollSpy = new bootstrap.ScrollSpy(document.querySelector('.scrollspy-example'), {
            target: '#navbar-help'
        });
        scrollSpy.refresh();
    });
</script>
@endsection
