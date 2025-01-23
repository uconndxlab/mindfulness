@extends('layouts.auth')

@section('title', 'Log In')

@section('content')
<div class="col-md-6">
    <form id="loginForm" method="POST" onsubmit="handleLogin(event)">
        @csrf
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                {{ session('success') }}
            </div>
        @endif
        @error('error')
            <div id="error" class="alert alert-danger" role="alert">
                {{ $message }}
            </div>
        @enderror
        <div id="errorDiv" class="alert alert-danger" style="display: none;" role="alert"></div>

        <div class="text-left fs-2 fw-bold mb-1">
            {{ config('app.name') }}
        </div>
        <div class="text-left fs-5 fw-bold mb-3">
            Log in to your Account
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold" for="email">Email</label>
            <input id="email" type="email" class="form-control @error('email') is-invalid @enderror @error('credentials') is-invalid @enderror" name="email" value="{{ old('email') }}">
            @error('email')
                <span class="invalid-feedback" role="alert">
                    <strong>{!! $message !!}</strong>
                </span>
            @enderror
        </div>

        <div class="form-group mb-3">
            <label class="fw-bold" for="password">Password</label>
            <input id="password" type="password" class="form-control @error('password') is-invalid @enderror @error('credentials') is-invalid @enderror" name="password">
            @error('password')
                <span class="invalid-feedback" role="alert">
                    <strong>{{ $message }}</strong>
                </span>
            @enderror
            @error('credentials')
                <span class="invalid-feedback" role="alert">
                    <strong>{!! $message !!}</strong>
                </span>
            @enderror
        </div>

        <div class="d-flex justify-content-between align-items-center">
            <a href="{{ route('password.request') }}" class="text-center text- mt-1 mb-2">Forgot Password?</a>

            <div class="form-check mt-1 mb-2">
                <input type="checkbox" class="form-check-input" id="remember" name="remember" checked>
                <label class="form-check-label" for="remember">Remember Me</label>
            </div>
        </div>

        <div class="form-group text-center">
            <button type="submit" class="btn btn-primary">LOG IN</button>
        </div>
    </form>
    @if (!getConfig('registration_locked', false))
        <div class="text-center mt-3">
            <hr class="my-4">
            <span class="text-muted">OR</span>
        </div>
        <div class="text-center mt-3">
            <a class="btn btn-info text-center" href="{{ route('register') }}">SIGN UP</a>
        </div>
    @endif
</div>
<script>
    localStorage.removeItem('token');

    async function handleLogin(event) {
        event.preventDefault();

        const form = document.getElementById('loginForm');
        const formData = new FormData(form);

        try {
            const response = await axios.post('{{ route('login') }}', {
                email: formData.get('email'),
                password: formData.get('password'),
                remember: formData.get('remember') ? true : false,
            });

            if (response.data.token) {
                localStorage.setItem('token', response.data.token);
                console.log('Token set in localStorage:', localStorage.getItem('token')); // Verify token is stored
                
                // Set the default header right after getting the token
                axios.defaults.headers.common['Authorization'] = `Bearer ${response.data.token}`;
                console.log('Axios headers set:', axios.defaults.headers.common['Authorization']);
                
                window.location.href = '{{ route('explore.home') }}';
            }
        } catch (error) {
            console.error(error);
            const errorDiv = document.getElementById('errorDiv');
            errorDiv.innerHTML = error.response?.data?.message || 'An error has occurred during login';
            errorDiv.style.display = 'block';
        }
    }
</script>
@endsection
