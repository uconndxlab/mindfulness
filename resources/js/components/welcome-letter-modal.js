function bindWelcomeLetterModal() {
    const overlay = document.getElementById('welcomeLetterModal');
    if (!overlay) return;

    function openLetterModal() {
        overlay.hidden = false;
        overlay.classList.add('is-open');
        document.body.classList.add('letter-modal-open');
    }

    function closeLetterModal() {
        overlay.classList.remove('is-open');
        overlay.hidden = true;
        document.body.classList.remove('letter-modal-open');
    }

    document.querySelectorAll('[data-open-letter-modal]').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            openLetterModal();
        });
        el.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                openLetterModal();
            }
        });
    });

    overlay.querySelectorAll('[data-close-letter-modal]').forEach((el) => {
        el.addEventListener('click', (e) => {
            e.preventDefault();
            closeLetterModal();
        });
    });

    overlay.addEventListener('click', (e) => {
        if (e.target === overlay) closeLetterModal();
    });

    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && overlay.classList.contains('is-open')) {
            closeLetterModal();
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindWelcomeLetterModal);
} else {
    bindWelcomeLetterModal();
}
