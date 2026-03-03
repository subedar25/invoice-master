<div id="receiptPreviewModal" class="modal fade show"
     style="display:none; background:rgba(0,0,0,.5);"
     tabindex="-1">

    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">Receipt Preview</h5>
                <button type="button"
                        class="btn-close receipt-close"></button>
            </div>

            <div class="modal-body text-center">
                <img id="receiptPreviewImage"
                     class="img-fluid rounded"
                     style="max-height:70vh">
            </div>

            <div class="modal-footer justify-content-between">
                <a id="receiptDownloadBtn"
                   class="btn btn-outline-primary"
                   download>
                    <i class="fa fa-download"></i> Download
                </a>

                <button class="btn btn-secondary receipt-close">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>
