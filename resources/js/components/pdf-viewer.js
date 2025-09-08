import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';

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
    }).catch((error) => {
        console.error('PDF rendering error:', error);
        
        let errorMessage = 'Unable to display PDF document.';
        
        if (error.message.includes('Setting up fake worker failed') || 
            error.message.includes('worker') || 
            error.message.includes('setup')) {
            errorMessage = 'PDF viewer is temporarily unavailable. Please try refreshing the page.';
        } else if (error.message.includes('Invalid PDF')) {
            errorMessage = 'The PDF file appears to be corrupted or invalid.';
        } else if (error.message.includes('Missing PDF')) {
            errorMessage = 'The requested PDF file could not be found.';
        }
        
        showPdfError(container, errorMessage);
        throw error;
    });
}

function initPdfViewer() {
    const container = document.getElementById('pdfContainer');
    if (!container) return;

    const pdfUrl = container.getAttribute('data-pdf-url');
    if (!pdfUrl) {
        showPdfError(container, 'No PDF file specified.');
        return;
    }

    renderPdfInto(container, pdfUrl).catch((e) => {
        showPdfError(container, 'Unable to display PDF document.');
    });
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPdfViewer);
} else {
    initPdfViewer();
}


