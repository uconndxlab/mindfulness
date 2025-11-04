import './bootstrap';

// router: load page-specific module based on body data attribute
document.addEventListener('DOMContentLoaded', async () => {
    const page = document.body?.dataset?.page;
    const imports = [];

    // Global UI behavior - not on auth pages
    if (page !== 'auth') {
        // page id is auth for all auth pages
        imports.push(import('./misc'));
        imports.push(import('./components/modal'));
    }

    // Page-based (when entire page is dedicated)
    if (page === 'help') imports.push(import('./pages/help'));
    if (page === 'auth-login-register') imports.push(import('./pages/auth-login-register'));
    if (page === 'auth-verify') imports.push(import('./pages/auth-verify'));
    if (page === 'module') imports.push(import('./pages/module'));
    if (page === 'activity') imports.push(import('./pages/activity'));

    // Component-based (when component is embedded within another page)
    if (document.getElementById('admin-sidebar')) imports.push(import('./components/admin-sidebar'));
    if (document.getElementById('audio-options-div')) imports.push(import('./components/voice-selector'));
    if (document.querySelector('.feedback-audio.js-audio')) imports.push(import('./components/feedback-audio'));
    if (document.getElementById('journalForm')) imports.push(import('./components/journal'));
    if (document.getElementById('library-root')) imports.push(import('./pages/library'));
    if (document.getElementById('lock_button_reg')) imports.push(import('./components/registration-lock'));
    if (document.getElementById('pdf-viewer')) imports.push(import('./components/pdf-viewer'));
    if (document.querySelector('.slide__audio.js-audio')) imports.push(import('./components/audio-player'));
    if (document.getElementById('timer-container')) imports.push(import('./components/timer'));
    if (document.getElementById('quizForm')) imports.push(import('./components/quiz/index.js'));
    
    try {
        await Promise.all(imports);
    } catch (e) {
        console.error('Failed to load page/component module(s)', { page, error: e });
    }
});