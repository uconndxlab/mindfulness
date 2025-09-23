<div id="pdf-viewer" class="d-flex align-items-center">
    <button class="btn btn-primary btn-workbook me-1" data-bs-toggle="modal" data-bs-target="#pdfModal">
        View Workbook
        <i class="bi bi-arrow-right"></i>
    </button>
</div>
<div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div id="pdfModalContent" class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="pdfModalLabel">Workbook: {{ $wbName }}</h3>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div id="pdfModalBody" class="modal-body">
                <div id="pdfContainer" class="pdf-container" data-pdf-url="{{ $fpath }}"></div>
                <template id="pdfErrorTemplate">
                    <div class="pdf-error-container">
                        <div class="pdf-error-content">
                            <div class="pdf-error-icon">
                                <i class="bi bi-exclamation-triangle"></i>
                            </div>
                            <h3 class="pdf-error-title">PDF Loading Error</h3>
                            <p class="pdf-error-message"></p>
                        </div>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>
