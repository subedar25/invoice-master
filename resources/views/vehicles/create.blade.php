<div class="container">
    <div class="card">
        <div class="card-body">
    <form id="vehicleCreateForm" action="{{ route('vehicles.store') }}" method="POST">
        @csrf
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-semibold">VIN
                <span class="text-danger">*</span>
            </label>
            <div class="col-sm-9">
                <input type="text" name="vin" maxlength="17" required class="form-control">
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-semibold">Driver</label>
            <div class="col-sm-9">
                <select name="driver_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}">{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-semibold">Description</label>
            <div class="col-sm-9">
                <textarea name="description" rows="3" required class="form-control"></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-semibold">Hitch</label>
            <div class="col-sm-9">
                <select name="hitch" class="form-select">
                    <option value="0">Has Hitch</option>
                    <option value="1">Not Hitch</option>
                </select>
            </div>
        </div>
        <!-- Active -->
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-semibold">Active</label>
            <div class="col-sm-9">
                <select name="active" class="form-select">
                    <option value="0">Inactive</option>
                    <option value="1" selected>Active</option>
                </select>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-semibold">Driver Side Sponsor</label>
            <div class="col-sm-9">
                <textarea name="driver_side_sponsor" class="form-control"></textarea>
            </div>
        </div>
        <div class="row mb-3">
            <label class="col-sm-3 col-form-label fw-semibold">Passenger Side Sponsor</label>
            <div class="col-sm-9">
                <textarea name="passenger_side_sponsor" class="form-control"></textarea>
            </div>
        </div>
        <!-- Buttons -->
        <div class="text-end mt-3">
            <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
            <button type="submit" class="btn btn-primary btn-sm">Create</button>
        </div>
    </form>
</div>
    </div>
</div>

<link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
<script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
{{-- <script>
document.getElementById('added_timestamp').value = new Date().toString();
</script> --}}
@push('scripts')
<script src="/js/vehicles-create.js"></script>
@endpush
