{{-- <script src="{{ asset('js/vehicle-expenses-create.js') }}"></script> --}}

<div class="container">

    <div class="card">
        <div class="card-body">

            <h5 class="mb-3 fw-bold">Add Vehicle Expense</h5>

            <form id="vehicleExpenseCreateForm"
                  action="{{ route('vehicles.expenses.store', $vehicle->id) }}"
                  method="POST"
                  enctype="multipart/form-data">

                @csrf

                <input type="hidden" name="vehicle_id" value="{{ $vehicle->id }}">

                {{-- Receipt --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">
                        Receipt
                    </label>
                    <div class="col-sm-9">
                        <input type="file"
                               name="file"
                               class="form-control"
                               accept="image/*,application/pdf">
                        <small class="text-muted">PDF / Image (optional)</small>
                    </div>
                </div>

                {{-- Total --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">
                        Total <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="number"
                               name="total"
                               step="0.01"
                               min="0"
                               class="form-control"
                               required>
                    </div>
                </div>

                {{-- Mileage --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">
                        Mileage <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="number"
                               name="mileage"
                               min="0"
                               class="form-control"
                               required>
                    </div>
                </div>

                {{-- Type --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">
                        Type <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <select name="type" class="form-select" required>
                            <option value="">-- Select --</option>
                            <option value="fuel">Fuel</option>
                            <option value="maintenance">Maintenance</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>

                {{-- Receipt Date --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">
                        Receipt Date <span class="text-danger">*</span>
                    </label>
                    <div class="col-sm-9">
                        <input type="date"
                               name="date"
                               class="form-control"
                               value="{{ now()->format('Y-m-d') }}"
                               required>
                    </div>
                </div>

                {{-- Notes --}}
                <div class="row mb-3">
                    <label class="col-sm-3 col-form-label fw-semibold">
                        Notes
                    </label>
                    <div class="col-sm-9">
                        <textarea name="notes"
                                  rows="3"
                                  class="form-control"
                                  placeholder="Optional notes"></textarea>
                    </div>
                </div>

                {{-- Buttons --}}
                <div class="text-end mt-4">
                    <button type="button"
                            class="btn btn-secondary btn-sm"
                            data-dismiss="modal">
                        Cancel
                    </button>

                    <button type="submit"
                            class="btn btn-primary btn-sm">
                        <i class="fa fa-save"></i> Save Expense
                    </button>
                </div>

            </form>

        </div>
    </div>

</div>
