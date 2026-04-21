import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';

const DESKTOP_BREAKPOINT = 1280;

const getWorkerSrc = () => {
    if (import.meta.env.DEV) {
        // dev - use worker from node_modules
        return new URL('pdfjs-dist/build/pdf.worker.mjs', import.meta.url).href;
    }
    
    // prod - use copied worker file
    return '/build/assets/pdf.worker.js';
};

// set worker source
pdfjsLib.GlobalWorkerOptions.workerSrc = getWorkerSrc();

function showPdfError(container, message) {
    const template = document.getElementById('pdfErrorTemplate');
    if (!template) {
        container.innerHTML = `<div class="alert alert-danger" role="alert">${message}</div>`;
        return;
    }
    const errorElement = template.content.cloneNode(true);
    
    const messageElement = errorElement.querySelector('.pdf-error-message');
    if (messageElement) {
        messageElement.textContent = message;
    }
    container.innerHTML = '';
    container.appendChild(errorElement);
}

async function renderPdfInto(container, pdfUrl) {
    const pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
    for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
        const page = await pdfDoc.getPage(pageNum);
        const viewport = page.getViewport({ scale: 1.5 });

        const pageDiv = document.createElement('div');
        pageDiv.classList.add('pdf-page');
        pageDiv.dataset.pageNum = pageNum;

        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        canvas.height = viewport.height;
        canvas.width = viewport.width;

        pageDiv.appendChild(canvas);
        container.appendChild(pageDiv);

        const renderContext = { canvasContext: context, viewport };
        await page.render(renderContext).promise;
    }
    return pdfDoc;
}

async function resolvePageNumber(pdfDoc, dest) {
    try {
        // could be a string or an array
        let destArray = dest;
        if (typeof dest === 'string') {
            destArray = await pdfDoc.getDestination(dest);
        }
        if (!Array.isArray(destArray) || destArray.length === 0) return null;
        const pageRef = destArray[0];
        const pageIndex = await pdfDoc.getPageIndex(pageRef);
        return pageIndex + 1;
    } catch (err) {
        return null;
    }
}

function flattenOutline(items, level, acc) {
    // flatten the outline from tree to list
    if (!items) return acc;
    for (const item of items) {
        acc.push({ title: item.title, dest: item.dest, level });
        if (item.items && item.items.length) {
            flattenOutline(item.items, level + 1, acc);
        }
    }
    return acc;
}

async function buildBookmarks(pdfDoc) {
    // get elements
    const listEl = document.querySelector('#pdfBookmarks .pdf-bookmarks-list');
    const toggleBtn = document.getElementById('pdfBookmarksToggle');
    const modalBody = document.getElementById('pdfModalBody');
    const drawer = document.getElementById('pdfBookmarks');
    if (!listEl || !toggleBtn || !modalBody || !drawer) return;

    // flatten outline
    const outline = await pdfDoc.getOutline();
    if (!outline || outline.length === 0) return;

    const flat = flattenOutline(outline, 0, []);

    // resolve page numbers
    const resolved = await Promise.all(
        flat.map(async (entry) => ({
            ...entry,
            page: entry.dest ? await resolvePageNumber(pdfDoc, entry.dest) : null,
        }))
    );

    // build list items
    const fragment = document.createDocumentFragment();
    for (const entry of resolved) {
        if (!entry.page) continue;
        const a = document.createElement('a');
        a.href = '#';
        a.className = 'pdf-bookmark-item';
        a.dataset.level = Math.min(entry.level, 3);
        a.dataset.page = entry.page;
        a.textContent = entry.title;
        fragment.appendChild(a);
    }

    if (!fragment.children.length) return;

    // reveal elements
    listEl.appendChild(fragment);
    drawer.classList.remove('d-none');
    toggleBtn.classList.remove('d-none');

    const setOpen = (open) => {
        modalBody.classList.toggle('bookmarks-open', open);
        toggleBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
        drawer.setAttribute('aria-hidden', open ? 'false' : 'true');
    };

    toggleBtn.addEventListener('click', () => {
        setOpen(!modalBody.classList.contains('bookmarks-open'));
        toggleBtn.blur();
    });

    // handle list item clicks
    listEl.addEventListener('click', (event) => {
        const target = event.target.closest('.pdf-bookmark-item');
        if (!target) return;
        event.preventDefault();
        const page = target.dataset.page;
        const pageEl = document.querySelector(`#pdfContainer .pdf-page[data-page-num="${page}"]`);
        if (pageEl) {
            pageEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
        if (window.innerWidth < DESKTOP_BREAKPOINT) {
            setOpen(false);
        }
    });

    // auto-open on desktop-sized screens
    if (window.innerWidth >= DESKTOP_BREAKPOINT) {
        setOpen(true);
    }

    // reset drawer state when modal closes so reopening is clean
    const modalEl = document.getElementById('pdfModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', () => {
            setOpen(window.innerWidth >= DESKTOP_BREAKPOINT);
            const container = document.getElementById('pdfContainer');
            if (container) container.scrollTop = 0;
        });
    }
}

function initPdfViewer() {
    const container = document.getElementById('pdfContainer');
    if (!container) return;

    const pdfUrl = container.getAttribute('data-pdf-url');
    if (!pdfUrl) {
        showPdfError(container, 'No PDF file specified.');
        return;
    }

    renderPdfInto(container, pdfUrl)
        .then((pdfDoc) => buildBookmarks(pdfDoc))
        .catch((error) => {
            console.error('PDF rendering error:', error);

            let errorMessage = 'Unable to display PDF document.';

            if (error.message && (
                error.message.includes('Setting up fake worker failed') ||
                error.message.includes('worker') ||
                error.message.includes('setup')
            )) {
                errorMessage = 'PDF viewer is temporarily unavailable. Please try refreshing the page.';
            } else if (error.message && error.message.includes('Invalid PDF')) {
                errorMessage = 'The PDF file appears to be corrupted or invalid.';
            } else if (error.message && error.message.includes('Missing PDF')) {
                errorMessage = 'The requested PDF file could not be found.';
            }

            showPdfError(container, errorMessage);
        });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPdfViewer);
} else {
    initPdfViewer();
}
