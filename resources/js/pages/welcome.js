function initWelcomeWizard() {
    const root = document.getElementById('welcome-wizard');
    if (!root) return;

    const titles = [
        'Welcome Letter',
        'A Brief Welcome from the Researcher',
        'App Tutorial',
    ];
    const descriptions = [
        'Tap the letter and scroll it as you read.',
        'Meet the researcher.',
        'A quick tour of the app and its main features.',
    ];
    const steps = Array.from(root.querySelectorAll('.welcome-step'));
    const titleEl = document.getElementById('welcome-title');
    const descEl = document.getElementById('welcome-desc');
    const prevBtn = document.getElementById('welcome-prev');
    const nextBtn = document.getElementById('welcome-next');
    const completeForm = document.getElementById('welcome-complete-form');
    const dots = Array.from(root.querySelectorAll('.welcome-dot'));
    const total = steps.length;
    let current = 1;

    function pauseVideos() {
        root.querySelectorAll('video').forEach((video) => {
            try { video.pause(); } catch (_) {}
        });
    }

    function render() {
        steps.forEach((step) => {
            const n = Number(step.dataset.step);
            step.classList.toggle('d-none', n !== current);
        });

        if (titleEl) titleEl.textContent = titles[current - 1] || '';
        if (descEl) descEl.textContent = descriptions[current - 1] || '';

        dots.forEach((dot) => {
            const n = Number(dot.dataset.dot);
            const active = n === current;
            dot.classList.toggle('active', active);
            if (active) dot.setAttribute('aria-current', 'step');
            else dot.removeAttribute('aria-current');
        });

        const isFirst = current === 1;
        const isLast = current === total;

        if (prevBtn) {
            prevBtn.classList.toggle('invisible', isFirst);
            prevBtn.disabled = isFirst;
        }

        if (nextBtn) {
            nextBtn.classList.toggle('d-none', isLast);
        }

        if (completeForm) {
            completeForm.classList.toggle('d-none', !isLast);
        }
    }

    prevBtn?.addEventListener('click', () => {
        if (current <= 1) return;
        pauseVideos();
        current -= 1;
        render();
    });

    nextBtn?.addEventListener('click', () => {
        if (current >= total) return;
        pauseVideos();
        current += 1;
        render();
    });

    render();
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initWelcomeWizard);
} else {
    initWelcomeWizard();
}
