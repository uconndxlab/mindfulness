// Modal functionality
const modal = document.getElementById('appModal');
const myModal = new bootstrap.Modal(modal);
const closeBtn = document.getElementById('closeBtn');
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
        onCancel = null
    } = options;
    
    document.getElementById('appModalLabel').innerHTML = label;
    closeBtn.innerHTML = closeLabel;

    if (body) {
        const modalBody = document.getElementById('appModalBody');
        modalBody.innerHTML = body;
        modalBody.style.display = 'block';
    }

    // set up media
    const modalMedia = document.getElementById('appModalImg');
    if (media) {
        modalMedia.src = media;
        modalMedia.style.display = 'block';
    }
    else {
        modalMedia.style.display = 'none';
    }

    // set up form
    const modalForm = document.getElementById('modalForm');
    const additionalBtn = document.getElementById('additionalBtn');
    const methodInput = document.getElementById('modalMethod');

    if (route) {
        modalForm.action = route;
        methodInput.value = method;
        additionalBtn.innerHTML = buttonLabel;
        additionalBtn.style.display = 'inline-block';
        // style button
        additionalBtn.className = 'btn';
        additionalBtn.classList.add(buttonClass);
    } else {
        additionalBtn.style.display = 'none';
    }

    // CANCEL HANDLER
    //handling cancel - call function and dispose modal
    if (currentCancelHandler) {
        // remove exisiting event listeners
        modal.removeEventListener('hidden.bs.modal', currentCancelHandler);
        closeBtn.removeEventListener('click', currentCancelHandler);
    }

    // rewrite cancel function
    currentCancelHandler = () => {
        if (onCancel) onCancel();
        myModal.hide();
    };

    // add new listeners
    modal.addEventListener('hidden.bs.modal', currentCancelHandler, { once: true });

    myModal.show();
}

function modalFreezeBackground() {
    // save position
    const scrollY = window.scrollY;
    document.body.style.position = 'fixed';
    document.body.style.top = `-${scrollY}px`;
    document.body.style.width = '100%';
}

function modalRestoreBackground() {
    // save position and remove
    const scrollY = document.body.style.top;
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.width = '';
    
    // restore position
    window.scrollTo({
        top: parseInt(scrollY || '0') * -1,
        behavior: 'instant'
    });
}

// handle modal background scrolling
if (modal) {
    // on open
    modal.addEventListener('shown.bs.modal', function() {
        modalFreezeBackground();
    });
    
    // restore position
    modal.addEventListener('hidden.bs.modal', function() {
        modalRestoreBackground();
    });
}

// handle session modal data
if (window.sessionModalData) {
    showModal(window.sessionModalData);
} 