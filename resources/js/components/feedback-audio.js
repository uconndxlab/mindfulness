function initFeedbackAudio() {
    const audioBlocks = document.querySelectorAll('.feedback-audio.js-audio');
    if (!audioBlocks.length) return;

    function formatTime(timeInSeconds) {
        const minutes = Math.floor(timeInSeconds / 60);
        const seconds = Math.floor(timeInSeconds % 60);
        return `${minutes}:${seconds.toString().padStart(2, '0')}`;
    }

    function initAudioPlayer(block) {
        const audio = block.querySelector('audio');
        const play = block.querySelector('.play-pause');
        const icon = block.querySelector('#icon');
        let isPlaying = false;

        if (!audio || !play || !icon) return;

        if (audio.duration) {
            const maxTime = block.querySelector('#max-time');
            if (maxTime) {
                maxTime.innerHTML = formatTime(audio.duration);
            }
        }

        audio.addEventListener('play', function() {
            if (!isPlaying) {
                block.classList.remove('paused');
                block.classList.add('playing');
                icon.classList.remove('bi-play');
                icon.classList.add('bi-pause');
                isPlaying = true;
            }
        });
        audio.addEventListener('pause', function() {
            if (isPlaying) {
                block.classList.remove('playing');
                block.classList.add('paused');
                icon.classList.remove('bi-pause');
                icon.classList.add('bi-play');
                isPlaying = false;
            }
        });

        play.addEventListener('click', function() {
            if (audio.paused) {
                document.querySelectorAll('.feedback-audio.js-audio audio').forEach(a => { try { a.pause(); } catch (_) {} });
                document.querySelectorAll('.feedback-audio.js-audio').forEach(p => { p.classList.remove('playing'); p.classList.add('paused'); });
                document.querySelectorAll('.feedback-audio.js-audio .play-pause #icon').forEach(i => { i.classList.remove('bi-pause'); i.classList.add('bi-play'); });
                audio.play();
                block.classList.remove('paused');
                block.classList.add('playing');
                icon.classList.remove('bi-play');
                icon.classList.add('bi-pause');
                isPlaying = true;
            } else {
                audio.pause();
                block.classList.remove('playing');
                block.classList.add('paused');
                icon.classList.remove('bi-pause');
                icon.classList.add('bi-play');
                isPlaying = false;
            }
        });

        audio.ontimeupdate = function() {
            const current = block.querySelector('#current-time');
            if (current) current.innerHTML = formatTime(audio.currentTime);
        };

        audio.onended = function() {
            audio.pause();
        };

        if (!audio.paused) {
            setTimeout(function() { audio.dispatchEvent(new Event('play')); }, 50);
        }
    }

    audioBlocks.forEach(block => initAudioPlayer(block));

    // playback speed dropdown
    document.querySelectorAll('.feedback-audio .dropdown-item').forEach(item => {
        item.addEventListener('click', function(event) {
            event.preventDefault();
            const speed = parseFloat(this.getAttribute('data-speed'));
            const audio = this.closest('.js-audio')?.querySelector('audio');
            if (audio) {
                audio.playbackRate = speed;
                console.log('Playback speed set to:', speed);
                const toggle = this.closest('.dropdown')?.querySelector('.dropdown-toggle');
                if (toggle) toggle.textContent = `Audio Speed: ${speed}x`;
            } else {
                console.error('Audio element not found!');
            }
        });
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initFeedbackAudio);
} else {
    initFeedbackAudio();
}


