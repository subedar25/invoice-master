<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Name</label>

    </div>
    <div class="col-md-8">
        <input type="text" class="form-control" id="name" name="name" value="{{ $droppoint->name ?? '' }}" required>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Client</label>
    </div>
    <div class="col-md-8">
        <select class="form-control" id="client_id" name="client_id" required>
            <option value="">Select a Client...</option>
            @if(isset($Clients))
            @foreach($Clients as $client)
            <option value="{{ $client->id }}" {{ (isset($droppoint) && $droppoint->client_id == $client->id) ? 'selected' : '' }}>
                {{ $client->name }}
            </option>
            @endforeach
            @endif
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Phone</label>

    </div>
    <div class="col-md-8">
         <input type="tel" class="form-control" id="phone" name="phone" value="{{ $droppoint->phone ?? '' }}">
    </div>
</div>


<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Line 1</label>

    </div>
    <div class="col-md-8">
         <input type="text" class="form-control" id="address_line1" name="address_line1" value="{{ $droppoint->address_line1 ?? '' }}">
    </div>
</div>


<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Line 2</label>

    </div>
    <div class="col-md-8">
        <input type="text" class="form-control" id="address_line2" name="address_line2" value="{{ $droppoint->address_line2 ?? '' }}">
    </div>
</div>


<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">City</label>

    </div>
    <div class="col-md-8">
        <input type="text" class="form-control" id="address_city" name="address_city" value="{{ $droppoint->address_city ?? '' }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">State</label>

    </div>
    <div class="col-md-8">
        <input type="text" class="form-control" id="address_state" name="address_state" value="{{ $droppoint->address_state ?? '' }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Zip Code</label>

    </div>
    <div class="col-md-8">
        <input type="text" class="form-control" id="address_zipcode" name="address_zipcode" value="{{ $droppoint->address_zipcode ?? '' }}">
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Country</label>

    </div>
    <div class="col-md-8">
          <input type="text" class="form-control" id="address_country" name="address_country" value="{{ $droppoint->address_country ?? '' }}">
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Type</label>

    </div>
    <div class="col-md-8">
       <select class="form-control select2 select2-multiple" id="type_multiple" name="type" >
            @if(isset($ClientTypes))
            @foreach($ClientTypes as $types)
               
                <option value="{{ $types->id }}" {{ (isset($droppoint->type) && is_array($droppoint->type) && in_array($types->id, $droppoint->type)) ? 'selected' : '' }}>
                    {{ $types->name }}
                </option>
            @endforeach
            @endif
        </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Traffic Type</label>

    </div>
    <div class="col-md-8">
        <select class="form-select form-control" id="traffic_type" name="traffic_type" required>
                <option value="">Select Traffic Type...</option>
                <option value="low" {{ (isset($droppoint) && $droppoint->traffic_type === 'low') ? 'selected' : '' }}>Low</option>
                <option value="normal" {{ (isset($droppoint) && $droppoint->traffic_type === 'normal') ? 'selected' : '' }}>Normal</option>
                <option value="high" {{ (isset($droppoint) && $droppoint->traffic_type === 'high') ? 'selected' : '' }}>High</option>
            </select>
    </div>
</div>

<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Notes</label>

    </div>
    <div class="col-md-8">
        <textarea class="form-control" id="notes" name="notes" rows="3">{{ $droppoint->notes ?? '' }}</textarea>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Open</label>

    </div>
    <div class="col-md-8">
         <select class="form-select form-control" id="open" name="open" required>
              <option></option>
                <option value="1" {{ (isset($droppoint) && $droppoint->open === 1) ? 'selected' : '' }}>Open</option>
                <option value="0" {{ (isset($droppoint) && $droppoint->open === 0) ? 'selected' : '' }}>Close</option>
            </select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Active</label>

    </div>
    <div class="col-md-8">
        <select class="form-select form-control" id="active" name="active" required>
              <option></option>
                <option value="1" {{ (isset($droppoint) && $droppoint->active === 1) ? 'selected' : '' }}>Active</option>
                <option value="0" {{ (isset($droppoint) && $droppoint->active === 0) ? 'selected' : '' }}>Inactive</option>
            </select>
    </div>
</div>
<div class="row mb-3">
    <div class="col-md-2">
        <label for="name" class="form-label">Seasonal</label>

    </div>
    <div class="col-md-8">
        <select class="form-select form-control" id="seasonal" name="seasonal" required>
            <option></option>
                <option value="1" {{ (isset($droppoint) && $droppoint->seasonal === 1) ? 'selected' : '' }}>Seasonal</option>
                <option value="0" {{ (isset($droppoint) && $droppoint->seasonal === 0) ? 'selected' : '' }}>Year Round</option>
            </select>
    </div>
</div>
