<div class="modal fade" id="appModal" tabindex="-1" aria-labelledby="appModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="appModalLabel"></h5>
            </div>
            <div id="appModalBody" class="modal-body d-none">
            </div>
            <img id="appModalImg" src="" alt="Example Image" class="img-fluid d-block mx-auto d-none">
            <div class="modal-footer d-flex justify-content-center">
                <form id="modalForm" method="POST" class="w-100">
                    @csrf
                    <input type="hidden" name="_method" id="modalMethod" value="POST">
                    <div class="d-grid">
                        <button type="submit" id="additionalBtn" class="btn btn-danger d-none"></button>
                        <button type="button" id="closeBtn" class="btn btn-dark" data-bs-dismiss="modal"></button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>