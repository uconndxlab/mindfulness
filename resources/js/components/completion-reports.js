function scrollToHighlightedRow() {
    const root = document.getElementById('completion-reports-table');
    if (!root) return;

    const hhId = root.dataset.highlightHhId?.trim();
    if (!hhId) return;

    const row = document.getElementById(`report-row-${hhId}`);
    if (row) {
        row.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
}

function registerLivewireHook() {
    if (window.__completionReportsHookRegistered) return;
    window.__completionReportsHookRegistered = true;

    Livewire.hook('morph.updated', ({ el }) => {
        const root = document.getElementById('completion-reports-table');
        if (!root) return;

        if (el === root || root.contains(el)) {
            scrollToHighlightedRow();
        }
    });
}

function initCompletionReports() {
    scrollToHighlightedRow();

    if (window.Livewire) {
        registerLivewireHook();
        return;
    }

    document.addEventListener('livewire:init', () => {
        scrollToHighlightedRow();
        registerLivewireHook();
    });
}

initCompletionReports();
