@extends('layouts.admin')

@section('title', 'Admin | Completion Reports')

@section('admin_content')
    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <h1 class="h2">Completion Reports</h1>
    </div>

    @livewire('completion-report-table')

    <div id="pdf-viewer" class="d-none"></div>
    <div class="modal fade" id="pdfModal" tabindex="-1" aria-labelledby="pdfModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div id="pdfModalContent" class="modal-content">
                <div class="modal-header">
                    <h3 class="modal-title" id="pdfModalLabel">Completion Report</h3>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div id="pdfModalBody" class="modal-body pdf-modal-body">
                    <aside id="pdfBookmarks" class="pdf-bookmarks d-none" aria-hidden="true">
                        <button type="button" id="pdfBookmarksToggle" class="pdf-bookmarks-tab" aria-label="Toggle bookmarks" aria-expanded="false" aria-controls="pdfBookmarks">
                            <i class="bi bi-list"></i>
                        </button>
                        <div class="pdf-bookmarks-inner">
                            <div class="pdf-bookmarks-header">Bookmarks</div>
                            <nav class="pdf-bookmarks-list"></nav>
                        </div>
                    </aside>
                    <div id="pdfContainer" class="pdf-container" data-pdf-url=""></div>
                    <div id="pdfLoading" class="pdf-loading" role="status" aria-live="polite">
                        <div class="spinner-border" aria-hidden="true"></div>
                        <span class="pdf-loading-text">Loading report&hellip;</span>
                    </div>
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
@endsection
