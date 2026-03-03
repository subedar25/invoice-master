@push('scripts')

<!-- jQuery MUST come first -->
{{-- <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script> --}}

<!-- SweetAlert -->
{{-- <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script> --}}

<script>
    window.VEHICLE_ID = {{ $vehicle->id }};
</script>
{{-- <script src="{{ asset('js/vehicle-expenses-index.js') }}"></script> --}}
<script src="{{ asset('js/vehicle-expenses-create.js') }}"></script>
<script src="{{ asset('js/vehicle-expenses-edit.js') }}"></script>

@endpush

<div class="container-fluid">

    <h4 class="fw-bold mb-3">Vehicle Expenses</h4>

    <table id="expensesTable" class="table table-bordered table-hover table-striped align-middle">
        <thead class="table-light">
        <tr>
            <th width="40"><input type="checkbox" id="selectAll"></th>
            <th>Receipt</th>
            <th>Total</th>
            <th>Mileage</th>
            <th>Type</th>
            <th>Notes</th>
            <th>Receipt Date</th>
            <th>Added Timestamp</th>
            <th>User</th>
        </tr>
        </thead>

        <tbody>
        @forelse ($expenses as $expense)
            <tr data-id="{{ $expense->id }}">
                {{-- <tr data-id="{{ $expense->id }}" data-expense-id="{{ $expense->id }}"> --}}

                <td>
                    <input type="checkbox" class="row-check">
                </td>

                {{-- RECEIPT (NOT INLINE EDIT) --}}
               {{-- <td class="receipt-cell"
    data-expense="{{ $expense->id }}"
    data-has-file="{{ $expense->file ? 1 : 0 }}">

    @if($expense->file)
        <a href="{{ route('files.show', $expense->file->id) }}"
           class="btn btn-sm btn-outline-primary"
           target="_blank">
            <i class="fa fa-paperclip"></i> View
        </a>
    @endif

</td> --}}

<td class="receipt-cell" data-field="receipt"
     data-expense="{{ $expense->id }}"
      data-has-file="{{ $expense->file ? 1 : 0 }}">

@php
    $file = $expense->file;
    $exists = $file && Storage::disk('private')->exists($file->file_name);
@endphp

@if($exists)
    <img
        src="{{ route('files.show', $file->id) }}"
        class="img-thumbnail receipt-preview"
        width="80"
        style="cursor:pointer"
        data-src="{{ route('files.show', $file->id) }}"
    >
@else
    <span class="text-muted"></span>
@endif
</td>
                <td class="inline-edit" data-field="total">
                    {{ number_format($expense->total, 2) }}
                </td>

                <td class="inline-edit" data-field="mileage">
                    {{ $expense->mileage }}
                </td>
            <td class="inline-edit" data-field="type">
            {{ ucfirst($expense->type) }}
            </td>

                <td class="inline-edit" data-field="notes">
                    {{ $expense->notes }}
                </td>
{{-- <td class="inline-edit" data-field="date">
    <input type="date" class="form-control fform-control-sm inline-input"
           value="{{ optional($expense->date)->format('Y-m-d') }}"
           data-field="date">
</td> --}}
<td class="inline-edit" data-field="date">
{{ optional($expense->date)->format('Y-m-d H:i') ?? '-' }}
</td>
                <td>
                    {{ optional($expense->created_at)->format('Y-m-d H:i') }}
                </td>
                <td>
                    {{ $expense->user->name }}
                </td>
            </tr>
        @empty
            {{-- <tr>
                <td colspan="9" class="text-center text-muted py-3">
                    No expenses found
                </td>
            </tr> --}}
            <tr class="dt-empty">
    <td></td> 
    <td colspan="1"></td> 
    <td colspan="1"></td> 
    <td colspan="1"></td> 
    <td colspan="1"></td> 
    <td colspan="1" class="text-center text-muted">
        <b><i>No expenses found</b></i>
    </td>
    <td></td> 
    <td></td> 
    <td></td> 
</tr>

        @endforelse
        </tbody>
    </table>

</div>
    {{-- vehicleExpenseModal --}}
{{-- <div class="modal fade"
     id="vehicleExpenseModal"
     tabindex="-1"
     aria-labelledby="vehicleExpenseModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="vehicleExpenseModalLabel">
                    Vehicle Expense
                </h5>

                <button type="button"
                        class="btn-close"
                        data-dismiss="modal"
                        aria-label="Close">
                </button>
            </div>

            <div class="modal-body">
              
            </div>

        </div>
    </div>
</div> --}}

{{-- vehicleExpenseModal --}}
<div class="modal fade"
     id="vehicleExpenseModal"
     tabindex="-1"
     role="dialog"
     aria-labelledby="vehicleExpenseModalLabel"
     aria-hidden="true">

    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title"
                    id="vehicleExpenseModalLabel">
                    Vehicle Expense
                </h5>

                <button type="button"
                        class="close"
                        data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                {{-- Content loaded via AJAX --}}
            </div>

        </div>
    </div>
</div>

{{-- ImagePreviewModal --}}
{{-- <div class="modal fade" id="receiptPreviewModal" tabindex="-1">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-body text-center">
        <img id="previewImage" class="img-fluid">
      </div>
    </div>
  </div>
</div> --}}

{{-- ReceiptPreviewModal --}}
{{-- <div id="receiptPreviewModal" class="modal fade show"
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
</div> --}}

{{-- ReceiptPreviewModal --}}
<div class="modal fade"
     id="receiptPreviewModal"
     tabindex="-1"
     role="dialog"
     aria-hidden="true">

    <div class="modal-dialog modal-lg modal-dialog-centered"
         role="document">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title">
                    Receipt Preview
                </h5>

                <button type="button"
                        class="close receipt-close"
                        data-dismiss="modal"
                        aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
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

                <button type="button"
                        class="btn btn-secondary receipt-close"
                        data-dismiss="modal">
                    Close
                </button>
            </div>

        </div>
    </div>
</div>

{{-- ReceiptUploadModal --}}
{{-- <div class="modal fade" id="ReceiptUploadModal" tabindex="-1"
     aria-labelledby="ReceiptUploadModalLabel"
     aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ReceiptUploadModalLabel">Upload Receipt</h5>
        <button type="button" class="btn-close" data-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        
        </div>
    </div>
    </div>
</div> --}}

<script>
window.inlineConfig = {
    url: "/vehicle-expenses/_ID_",
    method: "PATCH",

    fields: {
        total: "text",
        mileage: "text",
        type: "select",
        notes: "textarea",
        date: "date",
       
    },

    options: {
        type: [
            { id: 'fuel', label: 'Fuel' },
            { id: 'maintenance', label: 'Maintenance' },
            { id: 'other', label: 'Other' }
        ],
        // user_id: @json(
        //     $users->map(fn($u) => [
        //         'id'    => $u->id,
        //         'label' => $u->name
        //     ])
        // )
    }
};
</script>

{{-- @if($expense->file && Storage::disk('private')->exists($expense->file->file_name))
//     <img src="{{ route('files.show', $expense->file->id) }}" class="img-thumbnail" width="80">
// @else
//     <span class="text-muted">No receipt</span>
// @endif --}}

