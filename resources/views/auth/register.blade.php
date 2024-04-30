<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login</title>
    <!-- Include Bootstrap CSS -->
    <!-- Other inclusions can go here... -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>

<body>
<div class="container mt-5">
    <div class="row justify-content-center">
            <div class="col-md-4">
                <form method="POST" action="{{ route('register.submit') }}">
                    @csrf

                    <div class="form-group">
                        <label for="name">First Name</label>
                        <input id="name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}">
                        @error('name')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="email">Email</label>
                        <input id="email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">
                        @error('email')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <label for="password">Password</label>
                        <input id="password" type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                        @error('password')
                            <span class="invalid-feedback" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                        @enderror
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn btn-primary">SIGN UP</button>
                    </div>
                </form>

                <div class="text-center mt-3">
                    <hr class="my-4">
                    <span class="text-muted">OR</span>
                </div>

                <a href="{{ route('login') }}" class="text-center mt-3">Return to Login</a>
            </div>
        </div>
    </div>
</body>
</html>
