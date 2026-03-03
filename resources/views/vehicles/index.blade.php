@extends('layouts.custom-admin')

@section('title', 'Vehicles', 'bold')

@section('content')

<div class="container-fluid">
    <div class="card">
        <div class="card-body">

            <table id="vehiclesTable" class="table table-bordered table-striped table-hover w-100">
                <thead>
                    <tr>
                        <th><input type="checkbox" id="selectAll"></th>
                        <th>VIN</th>
                        <th>Description</th>
                        <th>Driver</th>
                        <th>Hitch</th>
                        <th>Active</th>
                        <th>Driver Side Sponsor</th>
                        <th>Passenger Side Sponsor</th>
                        <th>Added Timestamp</th>
                    </tr>
                </thead>

                <tbody>
                @foreach ($vehicles as $vehicle)
                    <tr data-id="{{ $vehicle->id }}">

                        <td><input type="checkbox" class="row-check"></td>

                        {{-- VIN --}}
                        {{-- <td class="inline-edit" data-field="vin">
                            {{ $vehicle->vin }}
                        </td> --}}
                        <td class="inline-edit" data-field="vin">
                        <a href="{{ route('entity.info', ['type' => 'vehicles', 'id' => $vehicle->id]) }}" class="entity-link">
                        {{ $vehicle->vin }}
                        </a>
                        </td>


                        {{-- Description --}}
                        <td class="inline-edit" data-field="description">
                        {{ Str::limit(strip_tags(preg_replace('/\s+/', ' ', $vehicle->description)), 200) }}
                        </td>


                        {{-- Driver (select) --}}
                        <td class="inline-edit" data-field="driver_id">
                            {{ $vehicle->driver->name ?? "Unassigned" }}
                        </td>

                        {{-- Hitch (boolean) --}}
                        <td class="inline-edit" data-field="hitch">
                            {{ $vehicle->hitch ? "Yes" : "No" }}
                        </td>

                        {{-- Active (boolean) --}}
                        <td class="inline-edit" data-field="active">
                            {{ $vehicle->active ? "Yes" : "No" }}
                        </td>

                        {{-- Driver Sponsor --}}
                        <td class="inline-edit" data-field="driver_side_sponsor">
                        {{ Str::limit(strip_tags(preg_replace('/\s+/', ' ', $vehicle->driver_side_sponsor)), 200) }}
                        </td>


                        {{-- Passenger Sponsor --}}
                        <td class="inline-edit" data-field="passenger_side_sponsor">
                        {{ Str::limit(strip_tags(preg_replace('/\s+/', ' ', $vehicle->passenger_side_sponsor)), 200) }}
                        </td>


                        {{-- Created at --}}
                        <td>{{ $vehicle->created_at }}</td>

                    </tr>
                @endforeach
                </tbody>
            </table>

        </div>
    </div>
</div>

<!-- MODAL  -->
<div class="modal fade" id="vehicleModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-header">
                <h5 class="modal-title" id="vehicleModalTitle">Loading...</h5>
                <button type="button" class="btn-close" data-dismiss="modal"></button>
            </div>

            <div class="modal-body" id="vehicleModalBody">Loading...</div>

        </div>
    </div>
</div>

@endsection


{{-- INLINE EDIT CONFIG --}}
{{-- <script>
window.inlineConfig = {
    updateUrl: "/vehicles/",
    fields: {
        vin: "text",
        description: "textarea",
        driver_id: "select",
        hitch: "boolean",
        active: "boolean",
        driver_side_sponsor: "textarea",
        passenger_side_sponsor: "textarea",
    },
    options: {
    driver_id: @json(
        $drivers->map(fn($d) => [
            "id" => $d->id,
            "label" => $d->name
            ])
        )
    }
};
</script> --}}
<script>
window.inlineConfig = {
    url: "/vehicles/_ID_",
    method: "PATCH",

    fields: {
        vin: "text",
        description: "textarea",
        driver_id: "select",
        hitch: "boolean",
        active: "boolean",
        driver_side_sponsor: "textarea",
        passenger_side_sponsor: "textarea",
    },

    options: {
        driver_id: @json(
            $drivers->map(fn ($d) => [
                "id"    => $d->id,
                "label" => $d->name,
            ])
        )
    }
};
</script>

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.bootstrap5.min.css">
@endpush

@push('scripts')
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.colVis.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>



{{-- <script src="/js/inline-edit.js"></script>
<script src="/js/vehicles-index.js"></script> --}}
@endpush
