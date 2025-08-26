import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';

// Configure worker based on environment
function getWorkerSrc() {
    if (import.meta.env.DEV) {
        // In development, try to use the worker directly via import
        try {
            return new URL('pdfjs-dist/build/pdf.worker.mjs', import.meta.url).href;
        } catch (e) {
            console.warn('Failed to load worker via URL in dev:', e);
        }
    }
    
    // Production: look for worker in build assets
    // Vite should copy/bundle the worker file to the build directory
    return '/build/assets/pdf.worker.js';
}

// Set worker source with comprehensive error handling
try {
    const workerSrc = getWorkerSrc();
    console.log('Setting PDF.js worker source to:', workerSrc);
    pdfjsLib.GlobalWorkerOptions.workerSrc = workerSrc;
} catch (error) {
    console.error('Failed to configure PDF.js worker:', error);
    // Let PDF.js handle worker creation internally (may show warnings but should work)
    pdfjsLib.GlobalWorkerOptions.workerSrc = null;
}

function renderPdfInto(container, pdfUrl) {
    return pdfjsLib.getDocument(pdfUrl).promise.then(async (pdfDoc) => {
        for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
            const page = await pdfDoc.getPage(pageNum);
            const viewport = page.getViewport({ scale: 1.5 });

            const pageDiv = document.createElement('div');
            pageDiv.classList.add('pdf-page');

            const canvas = document.createElement('canvas');
            const context = canvas.getContext('2d');
            canvas.height = viewport.height;
            canvas.width = viewport.width;

            pageDiv.appendChild(canvas);
            container.appendChild(pageDiv);

            const renderContext = { canvasContext: context, viewport };
            await page.render(renderContext).promise;
        }
    });
}

function initPdfViewer() {
    const container = document.getElementById('pdfContainer');
    if (!container) return;

    const pdfUrl = container.getAttribute('data-pdf-url');
    if (!pdfUrl) return;

    renderPdfInto(container, pdfUrl).catch((e) => {
        console.error('Error loading PDF:', e);
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPdfViewer);
} else {
    initPdfViewer();
}


