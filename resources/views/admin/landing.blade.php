<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<header>
    <a class="btn btn-link" href="{{ $back_route }}">< Back</a>
</header>
<span>
    <h2><strong>{{ $head }}</strong></h2>
    <div class="container mt-4">
        <a class="btn btn-success border disabled" href="#" disabled>Edit Modules</a>
        <a class="btn btn-success border" href="{{ route('users.list') }}">Edit App Access</a>
    </div>
</span>
</body>
</html>

