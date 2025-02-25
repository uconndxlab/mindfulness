<div class="d-flex align-items-center">
    <button class="btn btn-primary btn-workbook me-1" data-bs-toggle="modal" data-bs-target="#pdfModal">
        View Workbook
        <i class="bi bi-arrow-right"></i>
    </button>
    <a href="{{ $fpath }}" class="btn btn-icon" download="workbook.pdf">
        <i class="bi bi-download"></i>
    </a>
</div>

<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div id="pdfModalContent" class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="pdfModalLabel">Workbook: {{ $wbName }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="pdfModalBody" class="modal-body">
                <div id="pdfContainer" style="width: 100%; overflow: auto; border: 1px solid #ccc;"></div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.10.38/pdf.min.mjs" type="module"></script>
<script type="module">
    import * as pdfjsLib from 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.10.38/pdf.min.mjs';
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.10.38/pdf.worker.min.mjs';

    document.addEventListener('DOMContentLoaded', function() {
        console.log('PDF viewer loaded');
        const pdfUrl = '{{ $fpath }}';
        const pdfContainer = document.getElementById('pdfContainer');
        const pdfModal = document.getElementById('pdfModal');

        async function loadPdf() {
            try {
                const pdfDoc = await pdfjsLib.getDocument(pdfUrl).promise;
                // console.log('PDF loaded successfully!');
    
                for (let pageNum = 1; pageNum <= pdfDoc.numPages; pageNum++) {
                    const page = await pdfDoc.getPage(pageNum);
                    const viewport = page.getViewport({ scale: 1.5 });
    
                    // div
                    const pageDiv = document.createElement('div');
                    pageDiv.classList.add('pdf-page');
                    
                    // canvas
                    const pageCanvas = document.createElement('canvas');
                    const context = pageCanvas.getContext('2d');
                    pageCanvas.height = viewport.height;
                    pageCanvas.width = viewport.width;
    
                    // add canvas to div
                    pageDiv.appendChild(pageCanvas);
    
                    // add page to container
                    pdfContainer.appendChild(pageDiv);
    
                    const renderContext = {
                        canvasContext: context,
                        viewport: viewport
                    };
                    await page.render(renderContext).promise;
                }
            } catch (error) {
                console.error('Error loading PDF:', error);
            }
        }

        loadPdf();
    });
</script>
