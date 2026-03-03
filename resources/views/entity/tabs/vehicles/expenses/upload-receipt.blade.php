<form id="uploadReceiptForm"
      action="{{ route('vehicle-expenses.receipt.store', $expense->id) }}"
      method="POST"
      enctype="multipart/form-data">

    @csrf

    {{-- Upload area --}}
    <div class="mb-3">
        <label class="form-label fw-semibold mb-2">
            Upload Receipt
        </label>

        <div id="dropZone"
             class="border border-2 border-dashed rounded p-4 text-center cursor-pointer"
             style="transition: background-color .2s ease">

            <i class="fa fa-cloud-upload-alt fa-2x mb-2 text-muted"></i>

            <p class="mb-1 fw-semibold">
                Drag & drop receipt here
            </p>

            <small class="text-muted">
                or click to browse (PDF / Image)
            </small>

            <input type="file"
                   name="file"
                   id="receiptInput"
                   class="d-none"
                   accept="application/pdf,image/*"
                   required>
        </div>

        <div id="fileName"
             class="mt-2 text-success small d-none">
        </div>
    </div>

    {{-- Actions --}}
    <div class="d-flex justify-content-end gap-2">
        <button type="button"
                class="btn btn-secondary"
                data-dismiss="modal">
            Cancel
        </button>

        <button type="submit"
                class="btn btn-primary">
            <i class="fa fa-upload mr-1"></i>
            Upload
        </button>
    </div>
</form>

<script>
(function () {

    const form = document.getElementById('uploadReceiptForm');
    if (!form) return;

    const dropZone = document.getElementById('dropZone');
    const input    = document.getElementById('receiptInput');
    const fileName = document.getElementById('fileName');

    //  * DROP ZONE

    dropZone.addEventListener('click', () => input.click());

    dropZone.addEventListener('dragover', e => {
        e.preventDefault();
        dropZone.classList.add('bg-light');
    });

    dropZone.addEventListener('dragleave', () => {
        dropZone.classList.remove('bg-light');
    });

    dropZone.addEventListener('drop', e => {
        e.preventDefault();
        dropZone.classList.remove('bg-light');

        if (!e.dataTransfer.files.length) return;

        input.files = e.dataTransfer.files;
        showFileName();
    });

    input.addEventListener('change', showFileName);

    function showFileName() {
        if (!input.files.length) return;
        fileName.textContent = input.files[0].name;
        fileName.classList.remove('d-none');
    }

    //  * FORM SUBMIT

    form.addEventListener('submit', function (e) {
        e.preventDefault();

        const data = new FormData(form);

        fetch(form.action, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN':
                    document.querySelector('meta[name="csrf-token"]').content
            },
            body: data
        })
        .then(async response => {

            // ❌ HTTP error (500, 419, 403, etc.)
            if (!response.ok) {
                const text = await response.text();
                console.error('Server error response:', text);
                throw new Error('Server error');
            }

            // ❌ Not JSON
            const contentType = response.headers.get('content-type') || '';
            if (!contentType.includes('application/json')) {
                const text = await response.text();
                console.error('Non-JSON response:', text);
                throw new Error('Invalid response');
            }

            return response.json();
        })
        .then(res => {
            if (!res.success) throw res;

            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: res.message || 'Receipt uploaded',
                timer: 1500,
                showConfirmButton: false
            });

            $('#vehicleExpenseModal').modal('hide');

            // safest for now
            location.reload();
        })
        .catch(err => {
            console.error('Upload error:', err);
            Swal.fire(
                'Error',
                'Receipt uploaded but server returned an error. Check logs.',
                'error'
            );
        });
    });

})();
</script>
