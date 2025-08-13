import * as pdfjsLib from 'pdfjs-dist/build/pdf.mjs';
import pdfjsWorker from 'pdfjs-dist/build/pdf.worker.mjs?worker&url';

// Configure worker via URL so Vite handles it in dev and prod
pdfjsLib.GlobalWorkerOptions.workerSrc = pdfjsWorker;

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


