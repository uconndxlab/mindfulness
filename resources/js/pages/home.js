import { escapeHtml } from '../utils/escapeHtml.js';

function initHomeProgressBar() {
    const fill = document.querySelector('.home-progress-fill[data-progress]');
    if (!fill) {
        return;
    }

    const percent = Math.max(0, Math.min(100, Number(fill.dataset.progress) || 0));
    fill.style.width = `${percent}%`;
}

function bindHomeHandlers() {
    initHomeProgressBar();

    // handle clicks on locked modules
    document.addEventListener('click', function (e) {
        const lockedModule = e.target.closest('.locked-module-link');
        if (!lockedModule) return;
        
        e.preventDefault();
        
        const moduleName = lockedModule.getAttribute('data-module-name') || 'This module';
        
        if (window.showModal) {
            window.showModal({
                label: 'Module Locked',
                body: `<p><strong>${escapeHtml(moduleName)}</strong> is currently locked. Complete previous modules to unlock this one.</p>`,
                closeLabel: 'OK'
            });
        }
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindHomeHandlers);
} else {
    bindHomeHandlers();
}

