function initVoiceSelector() {
    const optionsDiv = document.getElementById('audio-options-div');
    const buttonGroup = document.getElementById('voice_button_group');
    const hiddenInput = document.getElementById('voice_select');

    if (!optionsDiv || !buttonGroup || !hiddenInput) return;

    // show the options
    optionsDiv.classList.remove('d-none');
    console.log('audio options shown');

    function pauseAllAudioPlayers() {
        // pause all audio players
        document.querySelectorAll('.slide__audio-player').forEach(audio => {
            try { audio.pause(); } catch (_) {}
        });
    }
    
    function selectVoice(voice, voiceSlug) {
        // update hidden input with slug (used for DOM operations)
        hiddenInput.value = voice;
        hiddenInput.dataset.voice = voiceSlug;

        // update active state on buttons
        document.querySelectorAll('.voice-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        const activeBtn = document.querySelector(`.voice-btn[data-voice="${voiceSlug}"]`);
        if (activeBtn) {
            activeBtn.classList.add('active');
        }

        // pause all audio players
        pauseAllAudioPlayers();

        // show/hide the audio content
        document.querySelectorAll('.content-main[voice]').forEach(div => {
            div.classList.toggle('d-none', div.getAttribute('voice') !== voiceSlug);
        });

        // setup media session for the new voice - always
        setTimeout(() => {
            if (window.audioPlayerControls) {
                window.audioPlayerControls.setupMediaSessionForPlayer(voiceSlug);
            }
        }, 50); // small delay for DOM updates

        console.log(`Switched to voice: ${voice}`);
    }

    // initialize voice selector using the slug from hidden input
    const initialVoice = hiddenInput.value;
    const initialVoiceSlug = hiddenInput.dataset.voice;
    if (initialVoice && initialVoiceSlug) {
        selectVoice(initialVoice, initialVoiceSlug);
    }

    // delegate click handling to avoid inline JS
    buttonGroup.addEventListener('click', function (e) {
        const btn = e.target.closest('.voice-btn');
        if (!btn) return;
        const voice = btn.dataset.voiceName;
        const voiceSlug = btn.dataset.voice;
        if (voice && voiceSlug) selectVoice(voice, voiceSlug);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initVoiceSelector);
} else {
    initVoiceSelector();
}
