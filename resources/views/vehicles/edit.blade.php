<div class="container-fluid">
    <div class="card">
        <div class="card-body">
    <form id="vehicleEditForm" action="{{ route('vehicles.update', $vehicle) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row mb-3">
            <label for="vin" class="col-sm-3 col-form-label fw-semibold">VIN</label>
            <div class="col-md-6">
                <input id="vin" type="text" name="vin" maxlength="17" required class="form-control"
                       value="{{ old('vin', $vehicle->vin) }}">
            </div>
        </div>

        <div class="row mb-3">
            <label for="driver_id" class="col-sm-3 col-form-label fw-semibold">Driver</label>
            <div class="col-md-6">
                <select id="driver_id" name="driver_id" class="form-select">
                    <option value="">-- None --</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" {{ (old('driver_id', $vehicle->driver_id) == $d->id) ? 'selected' : '' }}>
                            {{ $d->name }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="description" class="col-sm-3 col-form-label fw-semibold">Description</label>
            <div class="col-sm-9">
                <textarea id="description" name="description" rows="3" required class="form-control">{{ old('description', $vehicle->description) }}</textarea>
            </div>
        </div>

        <div class="row mb-3">
            <label for="hitch" class="col-sm-3 col-form-label fw-semibold">Hitch</label>
            <div class="col-sm-9">
                <select id="hitch" name="hitch" class="form-select">
                    <option value="0" {{ (old('hitch', $vehicle->hitch) == 0) ? 'selected' : '' }}>No Hitch</option>
                    <option value="1" {{ (old('hitch', $vehicle->hitch) == 1) ? 'selected' : '' }}>Has Hitch</option>
                </select>
            </div>
        </div>

        <!-- Active -->
        <div class="row mb-3">
            <label for="active" class="col-sm-3 col-form-label fw-semibold">Active</label>
            <div class="col-sm-9">
                <select id="active" name="active" class="form-select">
                    <option value="0" {{ (old('active', $vehicle->active) == 0) ? 'selected' : '' }}>Inactive</option>
                    <option value="1" {{ (old('active', $vehicle->active) == 1) ? 'selected' : '' }}>Active</option>
                </select>
            </div>
        </div>

        <div class="row mb-3">
            <label for="driver_side_sponsor" class="col-sm-3 col-form-label fw-semibold">Driver Side Sponsor</label>
            <div class="col-sm-9">
                <textarea id="driver_side_sponsor" name="driver_side_sponsor" class="form-control" rows="2">{{ old('driver_side_sponsor', $vehicle->driver_side_sponsor) }}</textarea>
            </div>
        </div>

        <div class="row mb-3">
            <label for="passenger_side_sponsor" class="col-sm-3 col-form-label fw-semibold">Passenger Side Sponsor</label>
            <div class="col-sm-9">
                <textarea id="passenger_side_sponsor" name="passenger_side_sponsor" class="form-control" rows="2">{{ old('passenger_side_sponsor', $vehicle->passenger_side_sponsor) }}</textarea>
            </div>
        </div>
 <!-- Buttons -->
    <div class="text-end mt-3">
        <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-primary btn-sm">
            {{ isset($duplicate) ? 'Duplicate Vehicle' : 'Update Vehicle' }}
        </button>
    </div>
    </form>
</div>
</div>
</div>


@push('scripts')
<script src="/js/vehicles-edit.js"></script>
@endpush
