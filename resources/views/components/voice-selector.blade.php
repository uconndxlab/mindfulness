<div class="col-6 mt-1" id="audio-options-div">
    <div class="form-group dropdown {{ $showDropdown ? '' : 'd-none' }}">
        <label class="fw-bold" for="voice_dropdown_button">
            Voice Selection:
        </label>
        <button id="voice_dropdown_button" class="btn btn-xlight dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            {{ $defaultVoice }}
        </button>
        <ul class="dropdown-menu" id="voice_dropdown">
            @foreach ($voices as $voice => $_)
                <li>
                    <button class="dropdown-item" type="button" value="{{ $voice }}" data-voice="{{ $voice }}">
                        {{ $voice }}
                    </button>
                </li>
            @endforeach
        </ul>
        <input type="hidden" id="voice_select" name="voice_select" value="{{ $defaultVoice }}">
    </div>
</div>
