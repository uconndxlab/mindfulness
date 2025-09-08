function initVoiceSelector() {
    const optionsDiv = document.getElementById('audio-options-div');
    const dropdown = document.getElementById('voice_dropdown');
    const dropdownButton = document.getElementById('voice_dropdown_button');
    const hiddenInput = document.getElementById('voice_select');

    if (!optionsDiv || !dropdown || !dropdownButton || !hiddenInput) return;

    // show the options
    optionsDiv.classList.remove('d-none');
    console.log('audio options shown');

    function pauseAllAudioPlayers() {
        // pause all audio players
        document.querySelectorAll('.slide__audio-player').forEach(audio => {
            try { audio.pause(); } catch (_) {}
        });
    }

    function selectVoice(voice) {
        // update dropdown text
        dropdownButton.textContent = voice;

        // update hidden input
        hiddenInput.value = voice;

        // pause all audio players
        pauseAllAudioPlayers();

        // show/hide the audio content
        document.querySelectorAll('.content-main[voice]').forEach(div => {
            div.classList.toggle('d-none', div.getAttribute('voice') !== voice);
        });

        console.log(`Switched to voice: ${voice}`);
    }

    // initialize voice selector using the existing default value from the hidden input
    const initialVoice = hiddenInput.value;
    if (initialVoice) selectVoice(initialVoice);

    // delegate click handling to avoid inline JS
    dropdown.addEventListener('click', function (e) {
        const btn = e.target.closest('.dropdown-item');
        if (!btn) return;
        const voice = btn.getAttribute('data-voice');
        if (voice) selectVoice(voice);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVoiceSelector);
} else {
    initVoiceSelector();
}
