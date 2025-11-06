import { escapeHtml } from '../utils/escapeHtml.js';

function bindHomeHandlers() {
    console.log('bindHomeHandlers');
    // handle clicks on locked modules
    document.addEventListener('click', function (e) {
        const lockedModule = e.target.closest('.locked-module-link');
        if (!lockedModule) return;
        
        e.preventDefault();
        
        const moduleName = lockedModule.getAttribute('data-module-name') || 'This module';
        
        if (window.showModal) {
            window.showModal({
                label: 'Module Locked',
                body: `<p><strong>${escapeHtml(moduleName)}</strong> is not yet unlocked. Complete previous modules to unlock this one.</p>`,
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

