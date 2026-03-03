@section('title', 'Vehicle Details')

<div class="container-fluid">

    {{-- ACTION BAR --}}
    <div class="d-flex justify-content-end mb-3">
        <button
            type="button"
            class="btn btn-primary"
            onclick="openVehicleModal('/vehicles/{{ $entity->id }}/edit', 'Edit Vehicle')">
            <i class="fa fa-edit"></i> Edit Vehicle
        </button>
    </div>

    {{-- =========================
        INFO TAB CONTENT
    ========================== --}}


        <div class="card">
            <div class="card-body">

                <table class="table table-bordered table-sm align-middle mb-0">
                    <tbody>

                        <tr>
                            <th width="220">VIN</th>
                            <td>{{ $entity->vin ?? '—' }}</td>
                        </tr>

                        <tr>
                            <th>Description</th>
                            <td>{{ $entity->description ?? '—' }}</td>
                        </tr>

                        <tr>
                            <th>Driver</th>
                            <td>{{ optional($entity->driver)->name ?? '—' }}</td>
                        </tr>

                        <tr>
                            <th>Hitch</th>
                            <td>{{ $entity->hitch ? 'Yes' : 'No' }}</td>
                        </tr>

                        <tr>
                            <th>Active</th>
                            <td>{{ $entity->active ? 'Yes' : 'No' }}</td>
                        </tr>

                        <tr>
                            <th>Driver Side Sponsor</th>
                            <td>{{ $entity->driver_side_sponsor ?? '—' }}</td>
                        </tr>

                        <tr>
                            <th>Passenger Side Sponsor</th>
                            <td>{{ $entity->passenger_side_sponsor ?? '—' }}</td>
                        </tr>

                        <tr>
                            <th>Added Timestamp</th>
                            <td>
                                {{ optional($entity->created_at)->format('d M Y, h:i A') ?? '—' }}
                            </td>
                        </tr>

                    </tbody>
                </table>

            </div>
        </div>





{{-- =========================
    VEHICLE EDIT MODAL
========================== --}}
<div class="modal fade" id="vehicleModal" tabindex="-1" aria-labelledby="vehicleModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="vehicleModalLabel">Edit Vehicle</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body" id="vehicleModalBody">
                Loading...
            </div>

        </div>
    </div>
</div>


{{-- SUCCESS MESSAGE --}}
@if(session('success'))
    <div class="alert alert-success mt-3">
        {{ session('success') }}
    </div>
@endif
