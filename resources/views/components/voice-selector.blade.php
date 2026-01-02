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
                @php
                    $voiceSlug = 'audio-'.\Illuminate\Support\Str::slug($voice);
                @endphp
                <li>
                    <button class="dropdown-item" type="button" value="{{ $voice }}" data-voice="{{ $voiceSlug }}">
                        {{ $voice }}
                    </button>
                </li>
            @endforeach
        </ul>
        @php
            $defaultVoiceSlug = 'audio-'.\Illuminate\Support\Str::slug($defaultVoice);
        @endphp
        <input type="hidden" id="voice_select" name="voice_select" value="{{ $defaultVoice }}" data-voice="{{ $defaultVoiceSlug }}">
    </div>
</div>
