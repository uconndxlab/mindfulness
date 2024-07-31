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
    <h2><strong>{{ $head }}</strong></h2>
    <div class="container mt-4">
        @foreach($big_list as $item)
        <div class="mb-3">
            <a class="btn btn-light border" href="{{ route($item_type.'.show', [$item_type.'_id' => $item->id]) }}">Edit {{ $item->name ?? $item->title }}</a>
            @if (isset($lost_type))
                <a class="btn btn-info" href="{{ route($lost_type.'.index', [$item_type.'_id' => $item->id]) }}">{{ $lost_type }} List</a>
            @endif
            <form action="{{ route($item_type.'.delete', [$item_type.'_id' => $item->id]) }}" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-danger disabled" onclick="return confirm('Are you sure you want to delete {{ $item->name ?? $item->title }}?')">-</button>
            </form>
        </div>
        @endforeach
        <a class="btn btn-primary disabled" href="{{ route($item_type.'.create') }}">+</a>
    </div>
    <div class="container mt-4">
        @if (isset($lost_list))
            @foreach ($lost_list as $lost_item)
                <div class="mb-3">
                    <a class="btn btn-light" href="{{ route($lost_type.'.index', [$lost_type.'_id' => $lost_item->id]) }}">Edit {{ $lost_item->name ?? $lost_item->title }}</a>
                    <form action="{{ route($lost_type.'.delete', [$lost_type.'_id' => $lost_item->id]) }}" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-danger disabled" onclick="return confirm('Are you sure you want to delete {{ $lost_item->name ?? $lost_item->title }}?')">-</button>
                    </form>
                </div>
            @endforeach
        @endif
    </div>
</span>

</body>
</html>

