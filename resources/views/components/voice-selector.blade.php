@php
    $teachers = App\Models\Teacher::all()->keyBy('voice_key');
@endphp

<div class="col-12 mt-1" id="audio-options-div">
    <div class="form-group {{ $showDropdown ? '' : 'd-none' }}">
        <label class="fw-bold d-block mb-3 fs-5">
            Voice Selection:
        </label>
        <div id="voice_button_group" class="d-flex flex-wrap gap-3 align-items-start">
            @foreach ($voices as $voice => $_)
                @php
                    // voice is original capitalized name (label)
                    $voiceKey = \Illuminate\Support\Str::slug($voice);
                    $audioSlug = 'audio-' . $voiceKey;
                    $isAI = $voiceKey === 'ai';
                    $teacher = $teachers[$voiceKey] ?? null;
                    $profilePicture = $isAI 
                        ? asset('flowers/Flower-5.svg') 
                        : ($teacher ? asset('profile_pictures/sq/' . $teacher->profile_picture) : asset('flowers/Flower-5.svg'));
                    $isDefault = $voice === $defaultVoice;
                @endphp
                <div class="voice-option text-center">
                    <button type="button" 
                        class="voice-btn {{ $isDefault ? 'active' : '' }}" 
                        data-voice="{{ $audioSlug }}"
                        data-voice-name="{{ $voice }}"
                        aria-label="Select {{ $voice }} voice">
                        <img src="{{ $profilePicture }}" 
                            alt="{{ $voice }}" 
                            class="voice-profile-img">
                    </button>
                    <div class="voice-label mt-1">{{ $voice }}</div>
                </div>
            @endforeach
        </div>
        @php
            $defaultVoiceKey = \Illuminate\Support\Str::slug($defaultVoice);
            $defaultAudioSlug = 'audio-' . $defaultVoiceKey;
        @endphp
        <input type="hidden" id="voice_select" name="voice_select" value="{{ $defaultVoiceKey }}" data-voice="{{ $defaultAudioSlug }}">
    </div>
</div>
