function bindModalHandlers() {
    const modal = document.getElementById('appModal');
    if (!modal || !window.bootstrap) return;

    const closeBtn = document.getElementById('closeBtn');
    const { Modal } = window.bootstrap;
    const myModal = new Modal(modal);

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

        document.getElementById('appModalLabel').innerHTML = label;
        closeBtn.innerHTML = closeLabel;

        if (body) {
            const modalBody = document.getElementById('appModalBody');
            modalBody.innerHTML = body;
            modalBody.style.display = 'block';
        }

        const modalMedia = document.getElementById('appModalImg');
        if (media) {
            modalMedia.src = media;
            modalMedia.style.display = 'block';
        } else {
            modalMedia.style.display = 'none';
        }

        const modalForm = document.getElementById('modalForm');
        const additionalBtn = document.getElementById('additionalBtn');
        const methodInput = document.getElementById('modalMethod');

        if (route) {
            modalForm.action = route;
            methodInput.value = method;
            additionalBtn.innerHTML = buttonLabel;
            additionalBtn.style.display = 'inline-block';
            additionalBtn.className = 'btn';
            additionalBtn.classList.add(buttonClass);
        } else {
            additionalBtn.style.display = 'none';
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

    // Handle modal background scrolling
    modal.addEventListener('shown.bs.modal', function () {
        modalFreezeBackground();
    });
    modal.addEventListener('hidden.bs.modal', function () {
        modalRestoreBackground();
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
