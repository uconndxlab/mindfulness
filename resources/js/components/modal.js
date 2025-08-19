function bindModalHandlers() {
    const modal = document.getElementById('appModal');
    if (!window.bootstrap) return;

    const closeBtn = document.getElementById('closeBtn');
    const { Modal } = window.bootstrap;
    const myModal = modal ? new Modal(modal) : null;

    let currentCancelHandler = null;

    function showModal(options = {}) {
        const {
            label = 'undefined',
            body = null,
            media = null,
            route = null,
            method = 'POST',
            buttonLabel = 'Continue',
            buttonClass = 'btn-primary',
            closeLabel = 'Close',
            onCancel = null,
        } = options;

        if (!modal || !myModal) return;

        document.getElementById('appModalLabel').innerHTML = label;
        closeBtn.innerHTML = closeLabel;

        if (body) {
            const modalBody = document.getElementById('appModalBody');
            modalBody.innerHTML = body;
            modalBody.classList.remove('d-none');
        }

        const modalMedia = document.getElementById('appModalImg');
        if (media) {
            modalMedia.src = media;
            modalMedia.classList.remove('d-none');
        } else {
            modalMedia.classList.add('d-none');
        }

        const modalForm = document.getElementById('modalForm');
        const additionalBtn = document.getElementById('additionalBtn');
        const methodInput = document.getElementById('modalMethod');

        if (route) {
            modalForm.action = route;
            methodInput.value = method;
            additionalBtn.innerHTML = buttonLabel;
            additionalBtn.classList.remove('d-none');
            additionalBtn.className = 'btn';
            additionalBtn.classList.add(buttonClass);
        } else {
            additionalBtn.classList.add('d-none');
        }

        // Cancel handler
        if (currentCancelHandler) {
            modal.removeEventListener('hidden.bs.modal', currentCancelHandler);
            closeBtn.removeEventListener('click', currentCancelHandler);
        }

        currentCancelHandler = () => {
            if (onCancel) onCancel();
            myModal.hide();
        };

        modal.addEventListener('hidden.bs.modal', currentCancelHandler, { once: true });

        myModal.show();
    }

    function modalFreezeBackground() {
        const scrollY = window.scrollY;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollY}px`;
        document.body.style.width = '100%';
    }

    function modalRestoreBackground() {
        const scrollY = document.body.style.top;
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.width = '';
        window.scrollTo({ top: parseInt(scrollY || '0') * -1, behavior: 'instant' });
    }

    // Handle modal background scrolling for app modal (if present)
    if (modal) {
        modal.addEventListener('shown.bs.modal', function () {
            modalFreezeBackground();
        });
        modal.addEventListener('hidden.bs.modal', function () {
            modalRestoreBackground();
        });
        
        // accessibility issue: remove focus before hiding modal
        modal.addEventListener('hide.bs.modal', function () {
            // remove focus from element in modal
            if (document.activeElement && modal.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        });
        
        // mousedown on backdrop
        modal.addEventListener('mousedown', function (event) {
            // if clicking directly on the modal backdrop (not on modal content)
            if (event.target === modal) {
                // remove focus from element in modal
                if (document.activeElement && modal.contains(document.activeElement)) {
                    document.activeElement.blur();
                }
            }
        });
    }

    // Also handle ANY Bootstrap modal globally (e.g., #pdfModal)
    document.addEventListener('shown.bs.modal', function () {
        modalFreezeBackground();
    });
    document.addEventListener('hidden.bs.modal', function () {
        // Only restore if there are no other open modals
        if (!document.querySelector('.modal.show')) {
            modalRestoreBackground();
        }
    });
    
    // accessibility issue: remove focus before hiding modal
    document.addEventListener('hide.bs.modal', function (event) {
        // remove focus from element in modal
        if (document.activeElement && event.target.contains(document.activeElement)) {
            document.activeElement.blur();
        }
    });
    
    // mousedown for modal backdrop
    document.addEventListener('mousedown', function (event) {
        // check if clicking on a modal backdrop
        if (event.target.classList.contains('modal') && event.target.classList.contains('fade')) {
            // remove focus from element in modal
            if (document.activeElement && event.target.contains(document.activeElement)) {
                document.activeElement.blur();
            }
        }
    });

    // Expose globally for other modules that call showModal()
    window.showModal = showModal;

    // Handle session modal data if present via meta tag (set in scripts.blade.php)
    const meta = document.querySelector('meta[name="session-modal-data"]');
    if (meta) {
        try {
            const data = JSON.parse(meta.getAttribute('content'));
            if (data) showModal(data);
        } catch (e) {}
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bindModalHandlers);
} else {
    bindModalHandlers();
}
