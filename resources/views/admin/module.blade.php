<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $module->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-rbsA2VBKQhggwzxH7pPCaAqO46MgnOM80zW1RWuH61DGLwZJEdK2Kadq2F9CUG65" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
</head>
<body>

<header>
    <a class="btn btn-link" href="{{ route('admin.home') }}">< Back</a>
</header>

@if (session('success'))
    <div class="alert alert-success" role="alert">
        {{ session('success') }}
    </div>
@elseif ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
<span>
    <h2><strong>{{ $module->name }}</strong></h2>
    <div class="container mt-4">
        @foreach($module->days as $day)
        <div class="mb-3">
            <a class="btn btn-light" href="{{ route('day.show', ['day_id' => $day->id]) }}">Edit {{ $day->name }}</a>
            <form action="{{ route('day.delete', ['day_id' => $day->id]) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger disabled" onclick="return confirm('Are you sure you want to delete {{ $day->name }}?')">-</button>
            </form>
        </div>
        @endforeach
        <a class="btn btn-primary disabled" href="{{ route('day.create') }}">+</a>
    </div>
    <div class="container mt-4">
        @foreach ($lost_activities as $activity)
            <div class="mb-3">
                <a class="btn btn-light" href="{{ route('activity.show', ['activity_id' => $activity->id]) }}">Edit {{ $activity->name }}</a>
                <form action="{{ route('activity.delete', ['activity_id' => $activity->id]) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger disabled" onclick="return confirm('Are you sure you want to delete {{ $activity->title }}?')">-</button>
                </form>
            </div>
        @endforeach
    </div>
</span>

</body>
</html>

