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

    // Component-based (when component is embedded within another page)
    if (document.getElementById('journalForm')) imports.push(import('./components/journal'));
    if (document.getElementById('admin-sidebar')) imports.push(import('./components/admin-sidebar'));
    if (document.getElementById('pdf-viewer')) imports.push(import('./components/pdf-viewer'));
    if (document.getElementById('lock_button_reg')) imports.push(import('./components/registration-lock'));

    try {
        await Promise.all(imports);
    } catch (e) {
        console.error('Failed to load page/component module(s)', { page, error: e });
    }
});