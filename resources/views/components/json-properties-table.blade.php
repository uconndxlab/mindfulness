@props(['data'])

<table class="table table-bordered table-sm">
    <tbody>
        @foreach ($data as $key => $value)
            <tr>
                <th class="w-25">{{ str($key)->headline() }}</th>
                <td>
                    @if (is_array($value) || is_object($value))
                        <x-json-properties-table :data="$value" />
                    @else
                        @if ($value === true || $value === false)
                            {{ $value ? 'Yes' : 'No' }}
                        @else
                            {{ $value }}
                        @endif
                    @endif
                </td>
            </tr>
        @endforeach
    </tbody>
</table> 