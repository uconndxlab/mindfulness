<div class="col-6 mt-1" id="audio-options-div" style="display: block;">
    <div class="form-group dropdown" data-display="{{ $showDropdown ? 'block' : 'none' }}" style="display: {{ $showDropdown ? 'block' : 'none' }}">
        <label class="fw-bold" for="voice_dropdown_button">
            Voice Selection:
        </label>
        <button id="voice_dropdown_button" class="btn btn-xlight dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            {{ $defaultVoice }}
        </button>
        <ul class="dropdown-menu" id="voice_dropdown">
            @foreach ($voices as $voice => $_)
                <li>
                    <button class="dropdown-item" type="button" value="{{ $voice }}" data-voice="{{ $voice }}" onClick="selectVoice('{{ $voice }}')">
                        {{ $voice }}
                    </button>
                </li>
            @endforeach
        </ul>
        <input type="hidden" id="voice_select" name="voice_select" value="{{ $defaultVoice }}">
    </div>
</div>
<script>
    // show the options
    document.getElementById('audio-options-div').style.display = 'block';
    console.log('audio options shown');
    
    function selectVoice(voice) {
        // update dropdown text
        document.getElementById('voice_dropdown_button').textContent = voice;
        
        // update hidden input
        document.getElementById('voice_select').value = voice;
        
        // pause all audio players
        pauseAllAudioPlayers();
        
        // show/hide the audio content
        document.querySelectorAll('.content-main[voice]').forEach(div => {
            div.style.display = div.getAttribute('voice') === voice ? 'block' : 'none';
        });
        
        console.log(`Switched to voice: ${voice}`);
    }
    
    function pauseAllAudioPlayers() {
        // pause all audio players
        document.querySelectorAll('.slide__audio-player').forEach(audio => {
            audio.pause();
        });
    }

    // initialize voice selector
    document.addEventListener('DOMContentLoaded', function() {
        selectVoice('{{ $defaultVoice }}');
    });
</script>
