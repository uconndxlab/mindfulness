@extends('layouts.auth')

@section('title', 'Email Verification')

@section('content')
    <div class="col-md-6">
        @if (session('message'))
            <div class="alert alert-success" role="alert">
                {{ session('message') }}
            </div>
        @endif

        @error('error')
            <div class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror

        <h1>Email Verification</h1>

        <p>Please check your email for a verification link.</p>
        <p>If you did not receive the email, click the button below to request another.</p>

        <!-- verification.resend -->
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="btn btn-primary">Resend Verification Email</button>
        </form>
    </div>
    <script>
        function checkVerification() {
            console.log('Checking verification status...');
            return new Promise((resolve, reject) => {
                axios.get('/check-verification')
                    .then(response => {
                        if (response.data.verified) {
                            window.location.href = '/welcome';
                        }
                        else {
                            console.log('Not verified yet...');
                        }
                        resolve(true);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        reject(false);
                    });
            });
        }

        const intervalId = setInterval(checkVerification, 3000);

        document.addEventListener('visibilitychange', () => {
            if (document.hidden) {
                clearInterval(intervalId);
            }
            else {
                setInterval(checkVerification, 3000);
            }
        });
    </script>
@endsection
