@extends('layouts.app')

@section('title', 'Account')

@section('content')
<div class="col-md-8">
    <h4 class="text-center fw-bold mt-5 mb-3">
        Progress
    </h4>
    @foreach ($modules as $module)
        <div class="prior-note">
            <div class="grey-note">
                <h5 class="fw-bold d-flex justify-content-between">
                    <span>{{ $module->name }}</span>
                </h5>
                <small class="{{ $module->completed ? 'fw-bold' : '' }}">{{ $module->daysCompleted }}/{{ $module->totalDays }} days completed</small>
            </div>
        </div>
    @endforeach

    @if (session('success'))
        <div class="alert alert-success" role="alert">
            {{ session('success') }}
        </div>
    @endif
    <form id="updateInfoForm" method="POST" action="{{ route('user.update.namePass') }}">
        @csrf
        @Method('put')
        <h4 class="text-center fw-bold mt-5 mb-3">
            My Account
        </h4>
        <div id="success-messages" class="alert alert-success contact-message" style="display: none;"></div>
        <div id="error-messages" class="alert alert-danger contact-message" style="display: none;"></div>
        <div class="form-group mb-3">
            <label class="fw-bold" for="name">Change Displayed Name</label>
            <input id="name" type="name" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ Auth::user()->name }}">
            <div id="error-messages-name" class="invalid-feedback contact-message fw-bold" style="display: none;"></div>
        </div>
        
        <div class="form-group mb-3">
            <label class="fw-bold" for="password">Change Password</label><br>
            <small>Enter your new password below (Must contain at least 8 characters, one uppercase letter, one lowercase letter, and one number)</small>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
            <div id="error-messages-password" class="invalid-feedback contact-message fw-bold" style="display: none;"></div>
        </div>
    
        <div class="form-group mb-3">
            <label class="fw-bold" for="oldPass">Enter Old Password to Confirm Changes</label><br>
            <small>Enter your current password to verify your identity.</small> 
            <input id="oldPass" type="password" class="form-control @error('oldPass') is-invalid @enderror" name="oldPass">
            <div id="error-messages-oldPass" class="invalid-feedback contact-message fw-bold" style="display: none;"></div>
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
                <a href="{{ route('admin.dashboard') }}" class="btn btn-secondary">ADMIN DASHBOARD</a>
            </div>
        </div>
    @endif
</div>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.21.1/axios.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const errDiv = document.getElementById('error-messages');
        const nameErrDiv = document.getElementById('error-messages-name');
        const passwordErrDiv = document.getElementById('error-messages-password');
        const oldPassErrDiv = document.getElementById('error-messages-oldPass');
        const successDiv = document.getElementById('success-messages');

        //form submission
        const updateForm = document.getElementById('updateInfoForm');
        updateForm.addEventListener('submit', function(e) {
            e.preventDefault();

            //check for changes
            var nameInput = document.getElementById('name');
            var newPassInput = document.getElementById('password');
            if (nameInput.value == ''  && newPassInput.value == '') {
                //do not submit
                return;
            }

            closeResponseMessages();
            const formData = new FormData(updateForm);
            return new Promise((resolve, reject) => {
                axios.put('/user/update/namePass', {
                    name: formData.get('name'),
                    password: formData.get('password'),
                    oldPass: formData.get('oldPass')
                })
                .then(function(response) {
                    clearInputs();
                    console.log('Form submitted successfully: ', response);
                    if (response.data?.success) {
                        console.log(response.data.success);
                        successDiv.textContent = response.data.success;
                        successDiv.style.display = 'block';
                    }
                    resolve(true);
                })
                .catch(function(error) {
                    console.error('Error submitting form: ', error);
                    clearInputs();
                    //display error
                    if (error.response?.data?.errors) {
                        if (error.response.data.errors.name) {
                            nameErrDiv.textContent = error.response.data.errors.name;
                            nameErrDiv.style.display = 'block';
                        }
                        if (error.response.data.errors.password) {
                            passwordErrDiv.textContent = error.response.data.errors.password;
                            passwordErrDiv.style.display = 'block';
                        }
                        if (error.response.data.errors.oldPass) {
                            oldPassErrDiv.textContent = error.response.data.errors.oldPass;
                            oldPassErrDiv.style.display = 'block';
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

        function clearInputs() {
            document.querySelectorAll('input').forEach(input => {
                if (input.id == 'name') {
                    //do nothing...
                } else {
                    input.value = '';
                }
            });
        }
    });
</script>
@endsection
